<?php
namespace App\Repositories;

use App\Models\Tasks;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;

class TasksRepository implements TasksRepositoryInterface {
	/** @var Tasks */
	private $tasks;

	/** @var RoomsRepInt */
	private $rooms;

	/**
	 * @param WebSocketData $storage
	 */
	public function __construct(Tasks $tasks, RoomsRepInt $rooms)
	{
		$this->tasks = $tasks;
		$this->rooms = $rooms;
	}

	/**
	 * @param  array{text: string, owner: string, room: string} $params
	 * @return Tasks|null
	 */
	public function create($params)
	{
		$room = $this->rooms->first([
			'owner' => $params['owner'],
			'hash' => $params['room'],
		]);

		if (is_null($room)) {
			return null;
		}

		$last = $this->rooms->getLastTask($room);

		if (is_null($last)) {
			$order = 1;
		} else {
			$order = $last->order + 1;
		}

		$task = $this->tasks->query()->create([
			'text' => $params['text'],
			'room_id' => $room->id,
			'order' => (int)$order,
		]);

		$this->rooms->sendToRoom($room->hash, [
			'action' => 'room.task.add',
			'task' => $task,
		]);

		return $task;
	}

	/**
	 * @param  array{id: int, user: string, text: string, order?: int} $params
	 * @return Tasks|null
	 */
	public function update($params)
	{
		$task = $this->first(['id' => $params['id']]);

		if (is_null($task) OR is_null($task->room)) {
			return null;
		}

		if ($task->room->isOwner($params['user'])) {
			if (isset($params['text'])) $task->text = $params['text'];
			if (isset($params['story_point'])) $task->story_point = $params['story_point'];
			if (isset($params['story_point_view'])) $task->story_point_view = $params['story_point_view'];
			if (isset($params['order'])) {
				$task->order = $params['order'];
			}
			$task->save();

			$this->rooms->sendToRoom($task->room->hash, [
				'action' => 'room.task.update',
				'task' => $task,
			]);

			// Если голосование уже начато, задача могла поменятся
			if ($task->room->haveActiveStage()) {
				$this->rooms->sendToRoom($task->room->hash, [
					'action' => 'room.vote.start',
					'stage' => $task->room->stage,
					'task' => $task->room->activeTask()->first(),
				]);
			}

			return $task;
		}
	}

	/**
	 * @param  int $id
	 * @param  int $newOrder
	 * @return Tasks|null
	 */
	public function updateOrder($id, $newOrder)
	{
		$task = $this->first(['id' => $id]);

		if (is_null($task) OR is_null($task->room)) {
			return null;
		}

		$task->order = $newOrder;
		$task->save();

		return $task;
	}

	/**
	 * @param  array{id: int, owner: string} $params
	 * @return mixed
	 */
	public function delete($params)
	{
		$task = $this->first(['id' => $params['id']]);

		if (is_null($task) OR is_null($task->room)) {
			return false;
		}

		if ($task->room->isOwner($params['owner'])) {
			$room = $task->room;
			$deleted = $this->tasks->query()->where('id', $params['id'])->delete();

			if ($deleted) {
				$this->rooms->sendToRoom($task->room->hash, [
					'action' => 'room.task.remove',
					'id' => $task->id,
				]);

				if ($room->stage === \App\Enums\StagesOfRoom::vote AND $room->active_task_id !== $room->getNextTask()->id) {
					$room->active_task_id = $room->getNextTask()->id;
					$room->save();

					$this->rooms->sendToRoom($room->hash, [
						'action' => 'room.vote.reset',
					]);

					$this->rooms->sendToRoom($room->hash, [
						'action' => 'room.vote.start',
						'stage' => $room->stage,
						'task' => $room->activeTask()->first(),
					]);
				}
			}

			return $deleted;
		}
	}

	/**
	 * @param  array{id?: int, owner?: string} $conditions
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function get($conditions)
	{
		$query = $this->tasks->query();

		foreach ($conditions as $field => $value) {
			$query->where($field, $value);
		}

		return $query->get();
	}

	/**
	 * @param  string $hash
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getAllFromRoom($hash)
	{
		$room = $this->rooms->first(['hash' => $hash]);
		return is_null($room) ? new \Illuminate\Database\Eloquent\Collection : $room->tasks()->get();
	}

	/**
	 * @param  array{id?: int, owner?: string} $conditions
	 * @return Tasks|null
	 */
	public function first($conditions)
	{
		$query = $this->tasks->query();

		foreach ($conditions as $field => $value) {
			$query->where($field, $value);
		}

		return $query->first();
	}

}
