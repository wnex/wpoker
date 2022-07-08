<?php
namespace App\Listeners\Room;

use App\Models\Connections;
use App\Listeners\SocketListeners;

class UserToggleHasVote extends SocketListeners
{
	/**
	 * Переключение возможности голосовать для пользователя
	 * 
	 * @param  array{room: string, id: string, value: bool}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$room = $this->rooms->first(['hash' => $data['room']]);
		if (is_null($room)) return;

		$owner_id = Connections::where('uid', $room->owner)->first()->id;

		if ($data['id'] === $client_id OR $owner_id === $client_id) {
			Connections::where('room_id', $data['room'])->where('id', $data['id'])->update([
				'vote->has_vote' => $data['value'],
			]);

			$this->rooms->sendToRoom($data['room'], [
				'action' => 'room.user.changeHasVote',
				'id' => $data['id'],
				'hasVote' => $data['value'],
			]);

			$this->event->dispatch('server.room.vote.finish', [$data['room']]);
		}
	}

}
