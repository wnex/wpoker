<?php

namespace App\Listeners;

use App\Listeners\SocketListeners;
use App\Models\Rooms;

class Close extends SocketListeners {

	/**
	 * Закрытие соединения
	 * 
	 * @param  array{room: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
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
		$this->repository->removeUser($client_id);
	}

}
