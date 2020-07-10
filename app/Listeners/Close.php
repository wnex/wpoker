<?php

namespace App\Listeners;

use App\Listeners\SocketListeners;
use App\Events\Workerman;
use App\Models\Rooms;

class Close extends SocketListeners {

	// Смена имени [room, name]
	public function handle($client_id) {
		foreach (Rooms::getAllRooms() as $hash => $users) {
			if (in_array($client_id, $users)) {
				Rooms::removeUser($hash, $client_id);
			}

			$this->sendToAll([
				'action' => 'room.left.user',
				'id' => $client_id,
			], $users, [$client_id]);
		}
		Workerman::removeUser($client_id);
	}

}
