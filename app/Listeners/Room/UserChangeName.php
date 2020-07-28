<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Models\Rooms;

class UserChangeName extends SocketListeners {

	/**
	 * Смена имени
	 * 
	 * @param  array{room: string, name: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id) {
		$this->repository->setUser($client_id, [
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
