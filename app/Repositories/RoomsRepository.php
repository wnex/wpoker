<?php
namespace App\Repositories;

use App\Models\Rooms;
use App\Models\Tasks;
use App\Services\WebSocketData;
use Illuminate\Support\Arr;
use Illuminate\Events\Dispatcher;

class RoomsRepository implements RoomsRepositoryInterface {
	/** @var WebSocketData */
	private $storage;

	/** @var Rooms */
	private $rooms;

	/** @var Dispatcher */
	private $event;

	/**
	 * @param WebSocketData $storage
	 */
	public function __construct(WebSocketData $storage, Rooms $rooms, Dispatcher $event)
	{
		$this->storage = $storage;
		$this->rooms = $rooms;
		$this->event = $event;
	}

	/**
	 * @param  array{name: string, owner: string} $params
	 * @return Rooms
	 */
	public function create($params)
	{
		return $this->rooms->query()->create($params);
	}

	/**
	 * @param  array{id?: int, owner?: string} $conditions
	 * @return mixed
	 */
	public function delete($conditions)
	{
		$query = $this->rooms->query();

		if (empty($conditions)) {
			throw new \Exception("Empty conditions");
		}

		foreach ($conditions as $field => $value) {
			$query->where($field, $value);
		}

		return $query->delete();
	}

	/**
	 * @param  array{id?: int, owner?: string} $conditions
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function get($conditions)
	{
		$query = $this->rooms->query();

		foreach ($conditions as $field => $value) {
			$query->where($field, $value);
		}

		return $query->get();
	}

	/**
	 * @param  array{id?: int, owner?: string, hash?: string} $conditions
	 * @return Rooms|null
	 */
	public function first($conditions)
	{
		$query = $this->rooms->query();

		foreach ($conditions as $field => $value) {
			$query->where($field, $value);
		}

		return $query->first();
	}

	/**
	 * @param  Rooms $room
	 * @return Tasks|null
	 */
	public function getLastTask($room)
	{
		return $room->tasks()->orderBy('order', 'desc')->first();
	}


	/**
	 * @todo Rename this method to getAllClientsByRooms
	 * 
	 * @return array
	 */
	public function getAllRooms()
	{
		return $this->storage->get('rooms', []);
	}

	/**
	 * @todo Rename this method to getClientsFromRoom
	 * 
	 * @param  string $hash
	 * @return array
	 */
	public function getUsers($hash)
	{
		return Arr::get($this->getAllRooms(), $hash, []);
	}

	/**
	 * @todo Rename this method to addClientToRoom
	 * 
	 * @param string $hash
	 * @param string $client_id
	 * @return void
	 */
	public function addUser($hash, $client_id)
	{
		if (!in_array($client_id, $this->getUsers($hash))) {
			$rooms = $this->getAllRooms();
			$room = $this->getUsers($hash);
			$room[] = $client_id;
			Arr::set($rooms, $hash, $room);
			$this->storage->put('rooms', $rooms);
		}
	}

	/**
	 * @todo Rename this method to removeClientFromRoom
	 * 
	 * @param  string $hash
	 * @param  string $client_id
	 * @return void
	 */
	public function removeUser($hash, $client_id)
	{
		if (in_array($client_id, $this->getUsers($hash))) {
			$rooms = $this->getAllRooms();
			$room = $this->getUsers($hash);
			unset($room[array_search($client_id, $room)]);
			Arr::set($rooms, $hash, $room);
			$this->storage->put('rooms', $rooms);
		}
	}

	/**
	 * @param  string $hash
	 * @param  array  $params
	 * @return void
	 */
	public function sendToRoom($hash, $params)
	{
		$users_in_room = $this->rooms->getUsers($hash);
		$this->event->dispatch('server.socket.sendToAll', [$params, $users_in_room]);
	}

}
