<?php
namespace App\Listeners\Room;

use App\Models\Connections;
use App\Listeners\SocketListeners;

class TaskApprove extends SocketListeners
{

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

		if ($task->room->clientIsOwner($client_id)) {
			$task->story_point = $data['point'];
			$task->story_point_view = $data['view'];
			$task->save();

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

			$this->event->dispatch('socket.room.vote.reset', [['room' => $task->room->hash], $client_id]);

			if (!is_null($task->room->getNextTask()->id)) {
				$this->event->dispatch('socket.room.vote.start', [['room' => $task->room->hash], $client_id]);
			}
		}
	}

}
