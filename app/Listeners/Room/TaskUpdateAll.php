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
		$room =$this->rooms->first(['hash' => $data['room']]);
		if (is_null($room)) return;

		$owner_id = Connections::where('uid', $room->owner)->first()->id;

		if ($owner_id === $client_id) {
			foreach ($data['tasks'] as $task) {
				$this->tasks->updateOrder($task['id'], $task['order']);
			}
		}

		$this->rooms->sendToRoom($data['room'], [
			'action' => 'room.task.update.all',
			'tasks' => $room->tasks,
		], [$client_id]);

		// Если голосование уже начато, задача могла поменятся
		if ($room->stage === StagesOfRoom::vote) {
			$room->stage = StagesOfRoom::vote;
			$room->active_task_id = $room->getNextTask()->id;
			$room->save();

			$this->rooms->sendToRoom($data['room'], [
				'action' => 'room.vote.start',
				'stage' => $room->stage,
				'task' => $room->activeTask()->first(),
			]);
		}
	}

}
