<?php
namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Repositories\ClientsRepository;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;
use Illuminate\Contracts\Events\Dispatcher;

class UserKick extends SocketListeners
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

		$owner_id = $this->clients->getOwnerId($room->owner);

		if ($owner_id === $client_id) {
			$this->rooms->removeClientFromRoom($data['room'], $data['id']);

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
