<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Events\Workerman;
use App\Models\Rooms;

class Vote extends SocketListeners {

	/**
	 * Оценка
	 * 
	 * @param  array{room: string, vote: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id) {
		Workerman::setUser($client_id, [
			'isVoted' => true,
			'vote' => $data['vote'],
		]);

		$users_in_room = Rooms::getUsers($data['room']);
		$this->sendToAll([
			'action' => 'room.vote',
			'id' => $client_id,
		], $users_in_room);

		$this->sendToCurrentClient([
			'action' => 'room.vote.you',
			'id' => $client_id,
			'vote' => $data['vote'],
		]);

		event('server.room.vote.finish', [$data['room']]);
	}

}
