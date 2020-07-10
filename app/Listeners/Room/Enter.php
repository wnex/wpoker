<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Events\Workerman;
use App\Models\Rooms;

class Enter extends SocketListeners {

	// Вход в комнату [room, name, user]
	public function handle($data, $client_id) {
		Workerman::setUser($client_id, [
			'id' => $client_id,
			'room' => $data['room'],
			'name' => $data['name'],
			'user' => $data['user'],
		]);

		Rooms::addUser($data['room'], $client_id);

		$room = Rooms::where('hash', $data['room'])->first();
		$owner_id = Workerman::getOwnerId($room->owner);

		$users_in_room = Rooms::getUsers($data['room']);
		$this->sendToAll([
			'action' => 'room.entered.user',
			'id' => $client_id,
			'name' => $data['name'],
			'isOwner' => $owner_id === $client_id,
		], $users_in_room, [$client_id]);

		$this->sendToCurrentClient([
			'action' => 'room.parameters',
			'id' => $client_id,
			'name' => $room->name,
			'users' => Workerman::getAllUsers($data['room']),
			'owner' => $owner_id,
			'stage' => $room->stage,
		]);
	}

}
