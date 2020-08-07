<?php
namespace App\Listeners\Room;

use App\Listeners\SocketListeners;
use App\Repositories\ClientsRepository;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;
use Illuminate\Contracts\Events\Dispatcher;

class Vote extends SocketListeners
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
	 * Оценка
	 * 
	 * @param  array{room: string, point: string, view: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$this->clients->setUser($client_id, [
			'isVoted' => true,
			'vote' => $data['point'],
			'voteView' => $data['view'],
		]);

		$this->rooms->sendToRoom($data['room'], [
			'action' => 'room.vote',
			'id' => $client_id,
		]);

		$this->sendToCurrentClient([
			'action' => 'room.vote.you',
			'id' => $client_id,
			'vote' => $data['point'],
			'voteView' => $data['view'],
		]);

		$this->event->dispatch('server.room.vote.finish', [$data['room']]);
	}

}
