<?php
namespace App\Listeners\Room;

use App\Models\Connections;
use App\Listeners\SocketListeners;

class UserKick extends SocketListeners
{
	/**
	 * Кик из комнаты
	 * 
	 * @param  array{room: string, id: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$room = $this->rooms->first(['hash' => $data['room']]);
		if (is_null($room)) return;

		$owner_id = Connections::where('uid', $room->owner)->first()->id;

		if ($owner_id === $client_id) {
			Connections::where('room_id', $data['room'])->where('id', $data['id'])->update(['room_id' => '']);

			$this->rooms->sendToRoom($data['room'], [
				'action' => 'room.left.user',
				'id' => $data['id'],
			]);

			$this->sendToAll([
				'action' => 'room.kicked.you',
			], [$data['id']]);

			$this->event->dispatch('server.room.vote.finish', [$data['room']]);
		}
	}

}
