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
	 * @param  array{id: int, owner: string, name?: string, password?: string} $params
	 * @return Rooms|null
	 */
	public function update($params);

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
	 * @old getAllRooms
	 * 
	 * @return array
	 */
	public function getAllClientsByRooms();

	/**
	 * @todo Rename this method to getClientsFromRoom
	 * @old getUsers
	 * 
	 * @param  string $hash
	 * @return array
	 */
	public function getClientsFromRoom($hash);

	/**
	 * @todo Rename this method to addClientToRoom
	 * @old addUser
	 * 
	 * @param string $hash
	 * @param string $client_id
	 * @return void
	 */
	public function addClientToRoom($hash, $client_id);

	/**
	 * @todo Rename this method to removeClientFromRoom
	 * @old removeUser
	 * 
	 * @param  string $hash
	 * @param  string $client_id
	 * @return void
	 */
	public  function removeClientFromRoom($hash, $client_id);

	/**
	 * @param  string $hash
	 * @param  array  $params
	 * @param  array  $exclude
	 * @return void
	 */
	public function sendToRoom($hash, $params, $exclude = []);
}
