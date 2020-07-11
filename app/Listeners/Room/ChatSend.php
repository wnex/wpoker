<?php

namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Events\Workerman;
use App\Models\Rooms;

class ChatSend extends SocketListeners {

	// Сообщение в чат [room, name, message]
	public function handle($data, $client_id) {
		$users_in_room = Rooms::getUsers($data['room']);
		$this->sendToAll([
			'action' => 'room.chat.message',
			'id' => uniqid(),
			'author_id' => $client_id,
			'author_name' => $data['name'],
			'message' => $data['message'],
			'date' => date('c'),
			'notification' => false,
		], $users_in_room);
	}

}
