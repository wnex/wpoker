<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Events\Workerman;
use App\Models\Rooms;

class UserKick extends SocketListeners {

	// Кик из комнаты [room, id]
	public function handle($data, $client_id) {
		$room = Rooms::where('hash', $data['room'])->first();
		$owner_id = Workerman::getOwnerId($room->owner);

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
