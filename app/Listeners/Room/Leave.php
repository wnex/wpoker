<?php
namespace App\Listeners\Room;

use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;
use Illuminate\Contracts\Events\Dispatcher;

class Leave
{
	/** @var RoomsRepInt */
	private $rooms;

	/** @var Dispatcher */
	private $event;

	public function __construct(RoomsRepInt $rooms, Dispatcher $event)
	{
		$this->rooms = $rooms;
		$this->event = $event;
	}

	/**
	 * Выход из комнаты
	 * 
	 * @param  array{room: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$this->rooms->removeClientFromRoom($data['room'], $client_id);

		$this->rooms->sendToRoom($data['room'], [
			'action' => 'room.left.user',
			'id' => $client_id,
		]);

		$this->event->dispatch('server.room.vote.finish', [$data['room']]);
	}

}
