<?php
namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Repositories\ClientsRepository;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;

class VoteReset extends SocketListeners
{
	/** @var RoomsRepInt */
	private $rooms;

	public function __construct(RoomsRepInt $rooms, ClientsRepository $clients)
	{
		$this->rooms = $rooms;
		parent::__construct($clients);
	}

	/**
	 * Сброс оценок
	 * 
	 * @param  array{room: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$room = $this->rooms->first(['hash' => $data['room']]);
		if (is_null($room)) return;

		$room->active_task_id = null;
		$room->stage = \App\Enums\StagesOfRoom::wait;
		$room->save();

		$users_in_room = $this->rooms->getClientsFromRoom($data['room']);
		foreach ($users_in_room as $user_id) {
			if (isset($this->clients->getUser($user_id)['vote'])) {
				$this->clients->setUser($user_id, [
					'isVoted' => null,
					'vote' => null,
				]);
			}
		}
		$this->rooms->sendToRoom($data['room'], [
			'action' => 'room.vote.reset',
		]);
	}

}
