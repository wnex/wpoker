<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Models\Tasks;
use App\Models\Rooms;

class TaskApprove extends SocketListeners {

	/**
	 * Подтверждение оценки
	 * 
	 * @param  array{id: string, point: string, view: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id) {
		$task = Tasks::where('id', $data['id'])->first();

		if (is_null($task) OR is_null($task->room)) {
			return;
		}

		$owner_id = $this->repository->getOwnerId($task->room->owner);

		if ($owner_id === $client_id) {
			$task->story_point = $data['point'];
			$task->story_point_view = $data['view'];
			$task->save();

			$task->room->stage = 2;
			$task->room->save();

			$users_in_room = Rooms::getUsers($task->room->hash);
			$this->sendToAll([
				'action' => 'room.task.approve',
				'id' => $data['id'],
				'point' => $data['point'],
				'view' => $data['view'],
			], $users_in_room);

			$this->sendToAll([
				'action' => 'room.chat.message',
				'id' => uniqid(),
				'author_id' => 0,
				'author_name' => 'Server notification',
				'message' => "Approved {$data['view']} story points",
				'date' => date('c'),
				'notification' => true,
			], $users_in_room);
		}
	}

}
