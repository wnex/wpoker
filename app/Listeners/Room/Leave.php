<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Events\Workerman;
use App\Models\Rooms;

class Leave extends SocketListeners {

	// Выход из комнаты [room]
	public function handle($data, $client_id) {
		Rooms::removeUser($data['room'], $client_id);

		$users_in_room = Rooms::getUsers($data['room']);
		$this->sendToAll([
			'action' => 'room.left.user',
			'id' => $client_id,
		], $users_in_room);

		event('server.room.vote.finish', [$data['room']]);
	}

}
