<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Models\Rooms;

class Vote extends SocketListeners {

	/**
	 * Оценка
	 * 
	 * @param  array{room: string, point: string, view: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id) {
		$this->repository->setUser($client_id, [
			'isVoted' => true,
			'vote' => $data['point'],
			'voteView' => $data['view'],
		]);

		$users_in_room = Rooms::getUsers($data['room']);
		$this->sendToAll([
			'action' => 'room.vote',
			'id' => $client_id,
		], $users_in_room);

		$this->sendToCurrentClient([
			'action' => 'room.vote.you',
			'id' => $client_id,
			'vote' => $data['point'],
			'voteView' => $data['view'],
		]);

		event('server.room.vote.finish', [$data['room']]);
	}

}
