<?php
namespace App\Listeners\Room;

use App\Enums\StagesOfRoom;
use App\Listeners\SocketListeners;
use App\Repositories\ClientsRepository;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;
use App\Repositories\TasksRepositoryInterface as TasksRepInt;

class TaskUpdateAll extends SocketListeners
{
	/** @var RoomsRepInt */
	private $rooms;

	/** @var TasksRepInt */
	private $tasks;

	public function __construct(RoomsRepInt $rooms, TasksRepInt $tasks, ClientsRepository $clients)
	{
		$this->rooms = $rooms;
		$this->tasks = $tasks;
		parent::__construct($clients);
	}

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

		$owner_id = $this->clients->getOwnerId($room->owner);

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
			$task = $room->getNextTask();

			$room->stage = StagesOfRoom::vote;
			$room->active_task_id = $task ? $task->id : null;
			$room->save();

			$this->rooms->sendToRoom($data['room'], [
				'action' => 'room.vote.start',
				'stage' => $room->stage,
				'task' => $room->activeTask()->first(),
			]);
		}
	}

}
