<?php
namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Repositories\ClientsRepository;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;

class UserChangeName extends SocketListeners
{
	/** @var RoomsRepInt */
	private $rooms;

	public function __construct(RoomsRepInt $rooms, ClientsRepository $clients)
	{
		$this->rooms = $rooms;
		parent::__construct($clients);
	}

	/**
	 * Смена имени
	 * 
	 * @param  array{room: string, name: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$this->clients->setUser($client_id, [
			'name' => $data['name'],
		]);

		$this->rooms->sendToRoom($data['room'], [
			'action' => 'room.user.changeName',
			'id' => $client_id,
			'name' => $data['name'],
		]);
	}

}
