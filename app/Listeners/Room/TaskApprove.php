<?php
namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Repositories\ClientsRepository;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;
use App\Repositories\TasksRepositoryInterface as TasksRepInt;

class TaskApprove extends SocketListeners
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
	 * Подтверждение оценки
	 * 
	 * @param  array{id: string, point: string, view: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$task = $this->tasks->first(['id' => (int)$data['id']]);

		if (is_null($task) OR is_null($task->room)) {
			return;
		}

		$owner_id = $this->clients->getOwnerId($task->room->owner);

		if ($owner_id === $client_id) {
			$task->story_point = $data['point'];
			$task->story_point_view = $data['view'];
			$task->save();

			$task->room->stage = 2;
			$task->room->save();

			$this->rooms->sendToRoom($task->room->hash, [
				'action' => 'room.task.approve',
				'id' => $data['id'],
				'point' => $data['point'],
				'view' => $data['view'],
			]);

			$this->rooms->sendToRoom($task->room->hash, [
				'action' => 'room.chat.message',
				'id' => uniqid(),
				'author_id' => 0,
				'author_name' => 'Server notification',
				'message' => "Approved {$data['view']} story points",
				'date' => date('c'),
				'notification' => true,
			]);
		}
	}

}
