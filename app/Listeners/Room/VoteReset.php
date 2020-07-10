<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Events\Workerman;
use App\Models\Rooms;

class VoteReset extends SocketListeners {

	// Сброс оценок [room]
	public function handle($data, $client_id) {
		$room = Rooms::where('hash', $data['room'])->first();
		$room->stage = 0;
		$room->save();

		$users_in_room = Rooms::getUsers($data['room']);
		foreach ($users_in_room as $user_id) {
			if (isset(Workerman::getUser($user_id)['vote'])) {
				Workerman::setUser($user_id, [
					'isVoted' => null,
					'vote' => null,
				]);
				//unset(self::$users[$user_id]['isVoted']);
				//unset(self::$users[$user_id]['vote']);
			}
		}
		$this->sendToAll([
			'action' => 'room.vote.reset',
		], $users_in_room);
	}

}
