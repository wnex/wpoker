<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Events\Workerman;
use App\Models\Rooms;

class VoteStart extends SocketListeners {

	// Старт голосования [room]
	public function handle($data, $client_id) {
		$room = Rooms::where('hash', $data['room'])->first();
		$task = $room->getNextTask();

		$room->stage = 1;
		$room->active_task_id = $task ? $task->id : null;
		$room->save();

		$users_in_room = Rooms::getUsers($data['room']);
		$this->sendToAll([
			'action' => 'room.vote.start',
			'stage' => $room->stage,
			'task' => $room->activeTask()->first(),
		], $users_in_room);
	}

}
