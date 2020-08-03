<?php

namespace App\Http\Controllers;

use App\Models\Rooms;
use App\Models\Tasks;
use App\Traits\SocketSendlerMessageTrait;
use App\Repositories\TasksRepositoryInterface as TasksRepInt;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;

class TasksController extends Controller {

	use SocketSendlerMessageTrait;

	/** @var TasksRepInt */
	private $tasks;

	/** @var RoomsRepInt */
	private $rooms;

	/**
	 * @param TasksRepInt $tasks
	 * @param RoomsRepInt $rooms
	 */
	public function __construct(TasksRepInt $tasks, RoomsRepInt $rooms)
	{
		$this->tasks = $tasks;
		$this->rooms = $rooms;
	}

	/**
	 * @param  array{user: string, room: string, text: string}  $params
	 * @return Tasks|null
	 */
	public function create($params)
	{
		return $this->tasks->create([
			'text' => $params['text'],
			'owner' => $params['user'],
			'room' => $params['room'],
		]);
	}

	/**
	 * @param  array{id: int, user: string, text: string} $params
	 * @return Tasks|null
	 */
	public function update($params)
	{
		return $this->tasks->update([
			'id' => $params['id'],
			'user' => $params['user'],
			'text' => $params['text'],
		]);
	}

	/**
	 * @param  array{id: int, user: string}  $params
	 * @return bool
	 */
	public function delete($params)
	{
		return $this->tasks->delete([
			'id' => $params['id'],
			'owner' => $params['user'],
		]);
	}

	/**
	 * @param  array{room: string}  $params
	 * @return array<Tasks>
	 */
	public function get($params)
	{
		return $this->tasks->getAllFromRoom($params['room'])->toArray();
	}

}
