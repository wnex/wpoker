<?php
namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Repositories\ClientsRepository;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;

class ChatSend extends SocketListeners
{
	/** @var RoomsRepInt */
	private $rooms;

	public function __construct(RoomsRepInt $rooms, ClientsRepository $clients)
	{
		$this->rooms = $rooms;
		parent::__construct($clients);
	}

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
			$data['name'] = $this->clients->getUser($client_id);
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
