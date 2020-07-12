<?php

namespace App\Http\Controllers;

use App\Models\Rooms;
use App\Models\Tasks;
use GatewayWorker\Lib\Gateway;
use Illuminate\Http\Request;

class TasksController extends Controller {

	public function create(array $params) {
		$room = Rooms::where('owner', $params['owner'])->where('hash', $params['room'])->first();

		if (is_null($room)) {
			return null;
		}

		$task = Tasks::create([
			'text' => $params['text'],
			'room_id' => $room->id,
			'order' => $params['order'],
		]);

		$users_in_room = Rooms::getUsers($room->hash);
		Gateway::sendToAll(json_encode([
			'action' => 'room.task.add',
			'task' => $task,
		]), $users_in_room);

		return $task;
	}

	public function update(array $params) {
		$task = Tasks::where('id', $params['id'])->with('room')->first();

		if ($task->room->owner === $params['owner']) {
			$task->text = $params['text'];
			$task->save();

			$users_in_room = Rooms::getUsers($task->room->hash);
			Gateway::sendToAll(json_encode([
				'action' => 'room.task.update',
				'task' => $task,
			]), $users_in_room);

			// Если голосование уже начато, задача могла поменятся
			if ($task->room->haveActiveStage()) {
				Gateway::sendToAll(json_encode([
					'action' => 'room.vote.start',
					'stage' => $task->room->stage,
					'task' => $task->room->activeTask()->first(),
				]), $users_in_room);
			}

			return $task;
		}
	}

	public function delete(array $params) {
		$task = Tasks::where('id', $params['id'])->with('room')->first();

		if ($task->room->owner === $params['owner']) {
			$users_in_room = Rooms::getUsers($task->room->hash);
			Gateway::sendToAll(json_encode([
				'action' => 'room.task.remove',
				'id' => $task->id,
			]), $users_in_room);

			return Tasks::where('id', $params['id'])->delete();
		}
	}

	public function get(array $params) {
		$room = Rooms::where('hash', $params['room'])->first();
		return $room->tasks;
	}

}
