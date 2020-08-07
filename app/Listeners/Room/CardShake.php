<?php
namespace App\Listeners\Room;

use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;

class CardShake
{
	/** @var RoomsRepInt */
	private $rooms;

	public function __construct(RoomsRepInt $rooms)
	{
		$this->rooms = $rooms;
	}

	/**
	 * Пасхалки
	 * 
	 * @param  array{room: string, view: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$this->rooms->sendToRoom($data['room'], [
			'action' => 'room.card.shake',
			'view' => $data['view'],
		]);
	}

}
