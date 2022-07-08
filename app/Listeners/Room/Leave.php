<?php
namespace App\Listeners\Room;

use App\Models\Connections;
use App\Listeners\SocketListeners;

class Leave extends SocketListeners
{
	/**
	 * Выход из комнаты
	 * 
	 * @param  array{room: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		Connections::where('room_id', $data['room'])->where('id', $client_id)->update(['room_id' => '']);

		$this->rooms->sendToRoom($data['room'], [
			'action' => 'room.left.user',
			'id' => $client_id,
		]);

		$this->leaveGroup($client_id, 'room:'.$data['room']);

		$this->event->dispatch('server.room.vote.finish', [$data['room']]);
	}
}
