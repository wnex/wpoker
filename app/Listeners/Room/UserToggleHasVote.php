<?php
namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Repositories\ClientsRepository;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;
use Illuminate\Contracts\Events\Dispatcher;

class UserToggleHasVote extends SocketListeners
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
	 * Переключение возможности голосовать для пользователя
	 * 
	 * @param  array{room: string, id: string, value: bool}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$room = $this->rooms->first(['hash' => $data['room']]);
		if (is_null($room)) return;

		$client = $this->clients->getUser($data['id']);
		$owner_id = $this->clients->getOwnerId($room->owner);

		if (!empty($client) AND ($data['id'] === $client_id OR $owner_id === $client_id)) {
			$this->clients->setUser($data['id'], [
				'hasVote' => $data['value'],
			]);

			$this->rooms->sendToRoom($data['room'], [
				'action' => 'room.user.changeHasVote',
				'id' => $data['id'],
				'hasVote' => $data['value'],
			]);

			$this->event->dispatch('server.room.vote.finish', [$data['room']]);
		}
	}

}
