<?php
namespace App\Repositories;

use App\Models\Rooms;
use App\Models\Connections;
use App\Services\WebSocketData;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;

class ConnectionsRepository {
	/** @var RoomsRepInt */
	private $rooms;

	/**
	 * @param RoomsRepInt $rooms
	 */
	public function __construct(RoomsRepInt $rooms)
	{
		$this->rooms = $rooms;
	}


}
