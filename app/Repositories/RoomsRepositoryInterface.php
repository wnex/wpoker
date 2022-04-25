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
	 * @param  array{id: int, owner: string, name?: string, password?: string, cardset?: string} $params
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
	 * @param  string $hash
	 * @param  array  $params
	 * @param  array  $exclude
	 * @return void
	 */
	public function sendToRoom($hash, $params, $exclude = []);
}
