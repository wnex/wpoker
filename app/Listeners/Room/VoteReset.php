<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Events\Workerman;
use App\Models\Rooms;

class VoteReset extends SocketListeners {

	/**
	 * Сброс оценок
	 * 
	 * @param  array{room: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id) {
		$room = Rooms::where('hash', $data['room'])->first();
		if (is_null($room)) return;

		$room->stage = 0;
		$room->save();

		$users_in_room = Rooms::getUsers($data['room']);
		foreach ($users_in_room as $user_id) {
			if (isset(Workerman::getUser($user_id)['vote'])) {
				Workerman::setUser($user_id, [
					'isVoted' => null,
					'vote' => null,
				]);
			}
		}
		$this->sendToAll([
			'action' => 'room.vote.reset',
		], $users_in_room);
	}

}
