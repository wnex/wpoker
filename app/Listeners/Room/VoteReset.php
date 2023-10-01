<?php
namespace App\Listeners\Room;

use App\Models\Connections;
use App\Listeners\SocketListeners;

class VoteReset extends SocketListeners
{
	/**
	 * Сброс оценок
	 * 
	 * @param  array{room: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$room = $this->rooms->first(['hash' => $data['room']]);
		if (is_null($room)) return;

		if ($room->clientIsOwner($client_id)) {
			$room->active_task_id = null;
			$room->stage = \App\Enums\StagesOfRoom::wait;
			$room->save();

			Connections::where('room_id', $data['room'])->update([
				'vote->is_voted' => false,
				'vote->value' => null,
				'vote->view' => null,
			]);

			$this->rooms->sendToRoom($data['room'], [
				'action' => 'room.vote.reset',
			]);
		}
	}

}
