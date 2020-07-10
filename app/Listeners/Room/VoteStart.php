<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Events\Workerman;
use App\Models\Rooms;

class VoteStart extends SocketListeners {

	// Старт голосования [room]
	public function handle($data, $client_id) {
		$room = Rooms::where('hash', $data['room'])->first();
		$room->stage = 1;
		$room->save();

		$users_in_room = Rooms::getUsers($data['room']);
		$this->sendToAll([
			'action' => 'room.vote.start',
		], $users_in_room);
	}

}
