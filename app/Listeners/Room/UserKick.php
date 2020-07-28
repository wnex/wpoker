<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Models\Rooms;

class UserKick extends SocketListeners {

	/**
	 * Кик из комнаты
	 * 
	 * @param  array{room: string, id: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id) {
		$room = Rooms::where('hash', $data['room'])->first();
		if (is_null($room)) return;

		$owner_id = $this->repository->getOwnerId($room->owner);

		if ($owner_id === $client_id) {
			Rooms::removeUser($data['room'], $data['id']);

			$users_in_room = Rooms::getUsers($data['room']);
			$this->sendToAll([
				'action' => 'room.left.user',
				'id' => $data['id'],
			], $users_in_room);

			$this->sendToAll([
				'action' => 'room.kicked.you',
			], [$data['id']]);

			event('server.room.vote.finish', [$data['room']]);
		}
	}

}
