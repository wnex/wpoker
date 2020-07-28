<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Models\Rooms;

class Enter extends SocketListeners {

	/**
	 * Вход в комнату
	 * 
	 * @param  array{room: string, name: string, user: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id) {
		$this->repository->setUser($client_id, [
			'id' => $client_id,
			'room' => $data['room'],
			'name' => $data['name'],
			'user' => $data['user'],
		]);

		Rooms::addUser($data['room'], $client_id);

		$room = Rooms::where('hash', $data['room'])->first();
		if (is_null($room)) return;

		$owner_id = $this->repository->getOwnerId($room->owner);

		$users_in_room = Rooms::getUsers($data['room']);
		$this->sendToAll([
			'action' => 'room.entered.user',
			'id' => $client_id,
			'name' => $data['name'],
			'isOwner' => $owner_id === $client_id,
		], $users_in_room, [$client_id]);

		$this->sendToCurrentClient([
			'action' => 'room.parameters',
			'client_id' => $client_id,
			'id' => $room->id,
			'name' => $room->name,
			'users' => $this->repository->getAllUsers($data['room'], $room->stage === 2),
			'owner' => $owner_id,
			'stage' => $room->stage,
			'task' => $room->activeTask()->first(),
		]);
	}

}