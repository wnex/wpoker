<?php
namespace App\Listeners\Room;

use App\Models\Rooms;
use App\Models\Connections;
use App\Listeners\SocketListeners;

class VoteFinish extends SocketListeners
{
	/**
	 * Окончание голосования
	 * 
	 * @param  string  $room_id
	 * @return void
	 */
	public function handle($room_id)
	{
		$query = Connections::where('room_id', $room_id)->where('active', true)->where('vote->has_vote', true);
		$connects_in_room = (clone $query)->count();
		$connects_with_vote_in_room = (clone $query)->whereNotNull('vote->value')->count();
		$room = Rooms::where('hash', $room_id)->first();

		// Все проголосовали
		if ($connects_with_vote_in_room !== 0 && $connects_in_room === $connects_with_vote_in_room) {
			$this->rooms->sendToRoom($room_id, [
				'action' => 'room.vote.final',
				'users' => $room->getRoomUsers(true),
			]);
		}
	}

}
