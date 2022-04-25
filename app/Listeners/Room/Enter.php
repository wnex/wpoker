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
		$oldConnect = Connections::where('id', '!=', $client_id)->where('uid', $data['uid'])->where('room_id', $data['room'])->first();
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

		$connect->room_id = $data['room'];
		$connect->save();

		$room = $this->rooms->first(['hash' => $data['room']]);
		if (is_null($room)) return;

		$isOwner = Connections::where('id', $client_id)->where('uid', $room->owner)->exists();
		$owner_id = Connections::where('uid', $room->owner)->where('room_id', $data['room'])->firstOrNew()->id;

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
			'owner' => $owner_id,
			'stage' => $room->stage,
			'hasPassword' => $room->hasPassword,
			'hasVote' => $connect->vote['has_vote'],
			'task' => $room->activeTask()->first(),
			'cardset' => $room->cardset,
		]);

		// если сервер уже хранил информацию о голосовании, то передаём её
		if ($connect->vote['is_voted']) {
			$this->rooms->sendToRoom($data['room'], [
				'action' => 'room.vote',
				'id' => $client_id,
			]);

			$this->sendToCurrentClient([
				'action' => 'room.vote.you',
				'id' => $client_id,
				'vote' => $connect->vote['value'],
				'voteView' => $connect->vote['view'],
			]);

			$this->event->dispatch('server.room.vote.finish', [$data['room']]);
		}
	}

}
