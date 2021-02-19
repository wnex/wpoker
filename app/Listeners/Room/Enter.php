<?php
namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Repositories\ClientsRepository;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;

class Enter extends SocketListeners
{
	/** @var RoomsRepInt */
	private $rooms;

	public function __construct(RoomsRepInt $rooms, ClientsRepository $clients)
	{
		$this->rooms = $rooms;
		parent::__construct($clients);
	}

	/**
	 * Вход в комнату
	 * 
	 * @param  array{room: string, name: string, user: string, password: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$this->clients->setUser($client_id, [
			'id' => $client_id,
			'room' => $data['room'],
			'name' => $data['name'],
			'user' => $data['user'],
			'hasVote' => true,
		]);

		$room = $this->rooms->first(['hash' => $data['room']]);
		if (is_null($room)) return;

		$owner_id = $this->clients->getOwnerId($room->owner);

		if ($owner_id !== $client_id AND !empty($room->password) AND $data['password'] !== $room->password) {
			$wrong = false;
			if (!empty($data['password']) AND $data['password'] !== $room->password) {
				$wrong = true;
			}

			$this->sendToCurrentClient([
				'action' => 'room.wait.password',
				'name' => $room->name,
				'wrong' => $wrong,
			]);

			return;
		}

		$this->rooms->addClientToRoom($data['room'], $client_id);

		$this->rooms->sendToRoom($data['room'], [
			'action' => 'room.entered.user',
			'id' => $client_id,
			'name' => $data['name'],
			'isOwner' => $owner_id === $client_id,
			'hasVote' => true,
		], [$client_id]);

		$this->sendToCurrentClient([
			'action' => 'room.parameters',
			'client_id' => $client_id,
			'id' => $room->id,
			'name' => $room->name,
			'users' => $this->clients->getAllUsers($data['room'], $room->stage === 2),
			'owner' => $owner_id,
			'stage' => $room->stage,
			'hasPassword' => $room->hasPassword,
			'hasVote' => true,
			'task' => $room->activeTask()->first(),
		]);
	}

}
