<?php
namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Models\Connections;

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
			$name = Connections::where('id', $client_id)->firstOrNew()->name;
			if (empty($name)) {
				$data['name'] = 'User #'.$client_id;
			} else {
				$data['name'] = $name;
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
