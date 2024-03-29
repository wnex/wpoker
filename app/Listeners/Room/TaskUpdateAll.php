<?php
namespace App\Listeners\Room;

use App\Models\Connections;
use App\Enums\StagesOfRoom;
use App\Listeners\SocketListeners;

class TaskUpdateAll extends SocketListeners
{
	/**
	 * Обновление всех тасков
	 * 
	 * @param  array{room: string, tasks: array}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$room = $this->rooms->first(['hash' => $data['room']]);
		if (is_null($room)) return;

		if ($room->clientIsOwner($client_id)) {
			foreach ($data['tasks'] as $task) {
				$this->tasks->updateOrder($task['id'], $task['order']);
			}

			$this->rooms->sendToRoom($data['room'], [
				'action' => 'room.task.update.all',
				'tasks' => $room->tasks,
			]);

			// Если голосование уже начато, задача могла поменятся
			if ($room->stage === StagesOfRoom::vote AND $room->active_task_id !== $room->getNextTask()->id) {
				$room->active_task_id = $room->getNextTask()->id;
				$room->save();

				$this->rooms->sendToRoom($data['room'], [
					'action' => 'room.vote.reset',
				]);

				$this->rooms->sendToRoom($data['room'], [
					'action' => 'room.vote.start',
					'stage' => $room->stage,
					'task' => $room->activeTask()->first(),
				]);
			}
		}
	}

}
