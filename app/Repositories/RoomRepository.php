<?php
namespace App\Repositories;

use App\Models\Rooms;
use Illuminate\Support\Arr;
use App\Services\WebSocketData;

class RoomRepository {

	/** @var WebSocketData */
	private $storage;

	/**
	 * @param WebSocketData $storage
	 */
	public function __construct(WebSocketData $storage) {
		$this->storage = $storage;
	}

	/**
	 * @return array
	 */
	public function getAllRooms() {
		return $this->storage->get('rooms', []);
	}

	/**
	 * @param  string $hash
	 * @return array
	 */
	public function getUsers($hash) {
		return Arr::get($this->getAllRooms(), $hash, []);
	}

	/**
	 * @param string $hash
	 * @param string $client_id
	 * @return void
	 */
	public function addUser($hash, $client_id) {
		if (!in_array($client_id, $this->getUsers($hash))) {
			$rooms = $this->getAllRooms();
			$room = $this->getUsers($hash);
			$room[] = $client_id;
			Arr::set($rooms, $hash, $room);
			$this->storage->put('rooms', $rooms);
		}
	}

	/**
	 * @param  string $hash
	 * @param  string $client_id
	 * @return void
	 */
	public  function removeUser($hash, $client_id) {
		if (in_array($client_id, $this->getUsers($hash))) {
			$rooms = $this->getAllRooms();
			$room = $this->getUsers($hash);
			unset($room[array_search($client_id, $room)]);
			Arr::set($rooms, $hash, $room);
			$this->storage->put('rooms', $rooms);
		}
	}

}