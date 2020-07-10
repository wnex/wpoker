<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Events\Workerman;
use App\Models\Rooms;

class UserChangeName extends SocketListeners {

	// Смена имени [room, name]
	public function handle($data, $client_id) {
		Workerman::setUser($client_id, [
			'name' => $data['name'],
		]);

		$users_in_room = Rooms::getUsers($data['room']);
		$this->sendToAll([
			'action' => 'room.user.changeName',
			'id' => $client_id,
			'name' => $data['name'],
		], $users_in_room);
	}

}
