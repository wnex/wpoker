<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Events\Workerman;
use App\Models\Rooms;
use App\Models\Tasks;

class TaskUpdateAll extends SocketListeners {

	// Обновление всех тасков [room, tasks]
	public function handle($data, $client_id) {
		$room = Rooms::where('hash', $data['room'])->first();
		$owner_id = Workerman::getOwnerId($room->owner);

		if ($owner_id === $client_id) {
			foreach ($data['tasks'] as $task) {
				Tasks::where('id', $task['id'])->update([
					'text' => $task['text'],
					'order' => $task['order'],
				]);
			}
		}

		$users_in_room = Rooms::getUsers($data['room']);
		$this->sendToAll([
			'action' => 'room.task.update.all',
			'tasks' => $room->tasks,
		], $users_in_room, [$client_id]);
	}

}
