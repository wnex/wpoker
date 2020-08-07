<?php
namespace App\Listeners;

use App\Listeners\SocketListeners;
use App\Repositories\ClientsRepository;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;
use Illuminate\Contracts\Events\Dispatcher;

class Close extends SocketListeners
{
	/** @var RoomsRepInt */
	private $rooms;

	/** @var Dispatcher */
	private $event;

	public function __construct(RoomsRepInt $rooms, Dispatcher $event, ClientsRepository $clients)
	{
		$this->rooms = $rooms;
		$this->event = $event;
		parent::__construct($clients);
	}

	/**
	 * Закрытие соединения
	 * 
	 * @param  array{room: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($client_id)
	{
		foreach ($this->rooms->getAllClientsByRooms() as $hash => $users) {
			if (in_array($client_id, $users)) {
				$this->rooms->removeClientFromRoom($hash, $client_id);
			}

			$this->sendToAll([
				'action' => 'room.left.user',
				'id' => $client_id,
			], $users, [$client_id]);
		}
		$this->clients->removeUser($client_id);

		$this->event->dispatch('server.online.update');
	}

}
