<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Models\Rooms;

class VoteFinish extends SocketListeners {

	/**
	 * Окончание голосования
	 * 
	 * @param  string  $room
	 * @return void
	 */
	public function handle($room) {
		$users_in_room = Rooms::getUsers($room);

		$voted = 0;
		foreach ($users_in_room as $user_id) {
			if (isset($this->repository->getUser($user_id)['vote'])) {
				$voted++;
			}
		}

		// Все проголосовали
		if ($voted === count($users_in_room)) {
			$this->sendToAll([
				'action' => 'room.vote.final',
				'users' => $this->repository->getAllUsers($room, true),
			], $users_in_room);
		}
	}

}
