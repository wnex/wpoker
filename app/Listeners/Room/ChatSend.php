<?php
namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Repositories\ClientsRepository;

class ChatSend extends SocketListeners
{
	/**
	 * Сообщение в чат
	 * 
	 * @param  array{room: string, name: string, message: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		if (empty($data['name'])) {
			if (empty($this->clients->getUser($client_id)['name'])) {
				$data['name'] = 'User #' . $this->clients->getUser($client_id)['id'];
			} else {
				$data['name'] = $this->clients->getUser($client_id)['name'];
			}
		}

		$this->rooms->sendToRoom($data['room'], [
			'action' => 'room.chat.message',
			'id' => uniqid(),
			'author_id' => $client_id,
			'author_name' => $data['name'],
			'message' => $data['message'],
			'date' => date('c'),
			'notification' => false,
		]);
	}

}
