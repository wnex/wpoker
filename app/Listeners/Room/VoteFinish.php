<?php
namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Repositories\ClientsRepository;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;

class VoteFinish extends SocketListeners
{
	/** @var RoomsRepInt */
	private $rooms;

	public function __construct(RoomsRepInt $rooms, ClientsRepository $clients)
	{
		$this->rooms = $rooms;
		parent::__construct($clients);
	}

	/**
	 * Окончание голосования
	 * 
	 * @param  string  $room
	 * @return void
	 */
	public function handle($room)
	{
		$users_in_room = $this->rooms->getClientsFromRoom($room);

		foreach ($users_in_room as $key => $user_id) {
			if (!$this->clients->getUser($user_id)['hasVote']) {
				unset($users_in_room[$key]);
			}
		}

		$voted = 0;
		foreach ($users_in_room as $user_id) {
			if (isset($this->clients->getUser($user_id)['vote'])) {
				$voted++;
			}
		}

		// Все проголосовали
		if ($voted !== 0 AND $voted === count($users_in_room)) {
			$this->rooms->sendToRoom($room, [
				'action' => 'room.vote.final',
				'users' => $this->clients->getAllUsers($room, true),
			]);
		}
	}

}
