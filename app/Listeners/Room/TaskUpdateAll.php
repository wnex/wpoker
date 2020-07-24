<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Events\Workerman;
use App\Models\Rooms;
use App\Models\Tasks;

class TaskUpdateAll extends SocketListeners {

	/**
	 * Обновление всех тасков
	 * 
	 * @param  array{room: string, tasks: array}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id) {
		$room = Rooms::where('hash', $data['room'])->first();
		if (is_null($room)) return;

		$owner_id = Workerman::getOwnerId($room->owner);

		if ($owner_id === $client_id) {
			foreach ($data['tasks'] as $task) {
				Tasks::where('id', (string)$task['id'])->update([
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

		// Если голосование уже начато, задача могла поменятся
		if ($room->stage === 1) {
			$task = $room->getNextTask();

			$room->stage = 1;
			$room->active_task_id = $task ? $task->id : null;
			$room->save();

			$this->sendToAll([
				'action' => 'room.vote.start',
				'stage' => $room->stage,
				'task' => $room->activeTask()->first(),
			], $users_in_room);
		}
	}

}
