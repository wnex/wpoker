<?php
namespace App\Repositories;

use App\Models\Rooms;
use App\Models\Tasks;

interface RoomsRepositoryInterface {
	/**
	 * @param  array{name: string, owner: string} $params
	 * @return Rooms
	 */
	public function create($params);

	/**
	 * @param  array{id?: int, owner?: string} $conditions
	 * @return mixed
	 */
	public function delete($conditions);

	/**
	 * @param  array{id?: int, owner?: string} $conditions
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function get($conditions);

	/**
	 * @param  array{id?: int, owner?: string} $conditions
	 * @return Rooms|null
	 */
	public function first($conditions);

	/**
	 * @param  Rooms $room
	 * @return Tasks|null
	 */
	public function getLastTask($room);

	/**
	 * @todo Rename this method to getAllClientsByRooms
	 * 
	 * @return array
	 */
	public function getAllRooms();

	/**
	 * @todo Rename this method to getClientsFromRoom
	 * 
	 * @param  string $hash
	 * @return array
	 */
	public function getUsers($hash);

	/**
	 * @todo Rename this method to addClientToRoom
	 * 
	 * @param string $hash
	 * @param string $client_id
	 * @return void
	 */
	public function addUser($hash, $client_id);

	/**
	 * @todo Rename this method to removeClientFromRoom
	 * 
	 * @param  string $hash
	 * @param  string $client_id
	 * @return void
	 */
	public  function removeUser($hash, $client_id);

	/**
	 * @param  string $hash
	 * @param  array  $params
	 * @return void
	 */
	public function sendToRoom($hash, $params);
}
