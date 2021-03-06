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

		$task = $room->getNextTask();

		$room->stage = 1;
		$room->active_task_id = $task ? $task->id : null;
		$room->save();

		$this->rooms->sendToRoom($data['room'], [
			'action' => 'room.vote.start',
			'stage' => $room->stage,
			'task' => $room->activeTask()->first(),
		]);
	}

}
