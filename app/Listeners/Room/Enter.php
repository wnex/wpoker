<?php
namespace App\Listeners\Room;

use App\Models\Connections;
use App\Listeners\SocketListeners;

class Enter extends SocketListeners
{
	/**
	 * Вход в комнату
	 * 
	 * @param  array{room: string, name: string, uid: string, password: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$room = $this->rooms->first(['hash' => $data['room']]);
		if (is_null($room)) return;

		$isOwner = $room->owner === $data['uid'];
		$owner_id = $isOwner ? $client_id : Connections::where('uid', $room->owner)->firstOrNew()->id;

		if (!$isOwner AND !empty($room->password) AND $data['password'] !== $room->password) {
			$wrong = false;
			if (!empty($data['password']) AND $data['password'] !== $room->password) {
				$wrong = true;
			}

			$this->sendToCurrentClient([
				'action' => 'room.wait.password',
				'name' => $room->name,
				'wrong' => $wrong,
			]);

			return;
		}

		$oldConnect = Connections::where('id', '!=', $client_id)
								->where('active', false)
								->where('uid', $data['uid'])
								->where('room_id', $data['room'])
								->first();
		$connect = Connections::where('id', $client_id)->first();

		// восстановление подключения
		if (isset($oldConnect)) {
			$connect->vote = $oldConnect->vote;
			$oldConnect->delete();

			$this->rooms->sendToRoom($data['room'], [
				'action' => 'room.left.user',
				'id' => $oldConnect->id,
			], [$client_id]);
		}

		$this->kickedOtherConnect($data['room'], $data['uid']);

		$connect->room_id = $data['room'];
		$connect->save();

		$this->joinGroup($client_id, 'room:'.$data['room']);

		$this->rooms->sendToRoom($data['room'], [
			'action' => 'room.entered.user',
			'id' => $client_id,
			'name' => $data['name'],
			'isOwner' => $isOwner,
			'hasVote' => $connect->vote['has_vote'],
		], [$client_id]);

		$this->sendToCurrentClient([
			'action' => 'room.parameters',
			'client_id' => $client_id,
			'id' => $room->id,
			'name' => $room->name,
			'users' => $room->getRoomUsers($room->stage === 2),
			'isOwner' => $isOwner,
			'stage' => $room->stage,
			'hasPassword' => $room->hasPassword,
			'hasVote' => $connect->vote['has_vote'],
			'task' => $room->activeTask()->first(),
			'cardset' => $room->cardset,
			'time' => strtotime($room->updated_at),
		]);

		$this->sendVote($data['room'], $connect);
	}

	protected function kickedOtherConnect($room, $uid)
	{
		$otherConnects = Connections::where('room_id', $room)->where('uid', $uid)->get();
		Connections::where('room_id', $room)->where('uid', $uid)->update(['room_id' => '']);

		foreach ($otherConnects as $otherConnect) {
			$this->rooms->sendToRoom($room, [
				'action' => 'room.left.user',
				'id' => $otherConnect->id,
			]);

			$this->sendToAll([
				'action' => 'room.kicked.you',
			], [$otherConnect->id]);
		}
	}

	protected function sendVote($room, $connect)
	{
		if ($connect->vote['is_voted']) {
			$this->rooms->sendToRoom($room, [
				'action' => 'room.vote',
				'id' => $connect->id,
			]);

			$this->sendToCurrentClient([
				'action' => 'room.vote.you',
				'id' => $connect->id,
				'vote' => $connect->vote['value'],
				'voteView' => $connect->vote['view'],
			]);

			$this->event->dispatch('server.room.vote.finish', [$room]);
		}
	}
}
