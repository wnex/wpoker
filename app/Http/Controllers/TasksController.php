<?php

namespace App\Http\Controllers;

use App\Models\Rooms;
use App\Models\Tasks;
use App\Traits\SocketSendlerMessageTrait;
use Illuminate\Http\Request;

class TasksController extends Controller {

	use SocketSendlerMessageTrait;

	/**
	 * @param  array{id: string, user: string, room: string, text: string, order: int}  $params
	 * @return Tasks|null
	 */
	public function create($params) {
		$room = Rooms::where('owner', $params['user'])->where('hash', $params['room'])->first();

		if (is_null($room)) {
			return null;
		}

		$task = Tasks::create([
			'text' => $params['text'],
			'room_id' => $room->id,
			'order' => $params['order'],
		]);

		$users_in_room = Rooms::getUsers($room->hash);
		$this->sendToAll([
			'action' => 'room.task.add',
			'task' => $task,
		], $users_in_room);

		return $task;
	}

	/**
	 * @param  array{id: int, user: string, text: string}  $params
	 * @return Tasks|null
	 */
	public function update($params) {
		$task = Tasks::where('id', $params['id'])->with('room')->first();

		if (is_null($task) OR is_null($task->room)) {
			return null;
		}

		if ($task->room->isOwner($params['user'])) {
			$task->text = $params['text'];
			$task->save();

			$users_in_room = Rooms::getUsers($task->room->hash);
			$this->sendToAll([
				'action' => 'room.task.update',
				'task' => $task,
			], $users_in_room);

			// Если голосование уже начато, задача могла поменятся
			if ($task->room->haveActiveStage()) {
				$this->sendToAll([
					'action' => 'room.vote.start',
					'stage' => $task->room->stage,
					'task' => $task->room->activeTask()->first(),
				], $users_in_room);
			}

			return $task;
		}
	}

	/**
	 * @param  array{id: int, user: string}  $params
	 * @return bool
	 */
	public function delete($params) {
		$task = Tasks::where('id', $params['id'])->with('room')->first();

		if (is_null($task) OR is_null($task->room)) {
			return false;
		}

		if ($task->room->isOwner($params['user'])) {
			$users_in_room = Rooms::getUsers($task->room->hash);
			$this->sendToAll([
				'action' => 'room.task.remove',
				'id' => $task->id,
			], $users_in_room);

			return Tasks::where('id', $params['id'])->delete();
		}
	}

	/**
	 * @param  array{room: string}  $params
	 * @return array<Tasks>
	 */
	public function get($params) {
		$room = Rooms::where('hash', $params['room'])->first();
		return is_null($room) ? [] : $room->tasks->toArray();
	}

}
