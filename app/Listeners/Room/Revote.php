<?php
namespace App\Listeners\Room;

use App\Models\Connections;
use App\Listeners\SocketListeners;

class Revote extends SocketListeners
{
	/**
	 * Переголосование
	 * 
	 * @param  array{room: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$room = $this->rooms->first(['hash' => $data['room']]);
		if (is_null($room)) return;

		Connections::where('room_id', $data['room'])->update([
			'vote->is_voted' => false,
			'vote->value' => null,
			'vote->view' => null,
		]);

		$this->rooms->sendToRoom($data['room'], [
			'action' => 'room.vote.revote',
			'stage' => $room->stage,
			'task' => $room->activeTask()->first(),
		]);
	}

}
