<?php
namespace App\Repositories;

use App\Models\Tasks;

interface TasksRepositoryInterface {
	/**
	 * @param  array{text: string, owner: string, room: string} $params
	 * @return Tasks|null
	 */
	public function create($params);

	/**
	 * @param  array{id: int, user: string, text: string, order?: int} $params
	 * @return Tasks|null
	 */
	public function update($params);

	/**
	 * @param  int $id
	 * @param  int $newOrder
	 * @return Tasks|null
	 */
	public function updateOrder($id, $newOrder);

	/**
	 * @param  array{id: int, owner: string} $params
	 * @return mixed
	 */
	public function delete($params);

	/**
	 * @param  array{id?: int, owner?: string} $conditions
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function get($conditions);

	/**
	 * @param  string $hash
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getAllFromRoom($hash);

	/**
	 * @param  array{id?: int, owner?: string} $conditions
	 * @return Tasks|null
	 */
	public function first($conditions);
}
