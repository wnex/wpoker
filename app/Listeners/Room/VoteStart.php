<?php
namespace App\Listeners\Room;

use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;

class VoteStart
{
	/** @var RoomsRepInt */
	private $rooms;

	public function __construct(RoomsRepInt $rooms)
	{
		$this->rooms = $rooms;
	}

	/**
	 * Старт голосования
	 * 
	 * @param  array{room: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$room = $this->rooms->first(['hash' => $data['room']]);
		if (is_null($room)) return;

		if ($room->clientIsOwner($client_id)) {
			$room->stage = \App\Enums\StagesOfRoom::vote;
			$room->active_task_id = $room->getNextTask()->id;
			$room->save();
			$room->touch();

			$this->rooms->sendToRoom($data['room'], [
				'action' => 'room.vote.start',
				'stage' => $room->stage,
				'task' => $room->activeTask()->first(),
			]);
		}
	}

}
