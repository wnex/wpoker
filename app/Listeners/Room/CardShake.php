<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Events\Workerman;
use App\Models\Rooms;

class CardShake extends SocketListeners {

	/**
	 * Пасхалки
	 * 
	 * @param  array{room: string, point: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id) {
		$users_in_room = Rooms::getUsers($data['room']);
		$this->sendToAll([
			'action' => 'room.card.shake',
			'point' => $data['point'],
		], $users_in_room);
	}

}
