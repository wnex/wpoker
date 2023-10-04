<?php

namespace App\Http\Controllers;

use App\Models\Rooms;
use App\Repositories\RoomsRepositoryInterface;

class RoomsController extends Controller {

	/** @var RoomsRepositoryInterface */
	private $rooms;

	/**
	 * @param RoomsRepositoryInterface $rooms
	 */
	public function __construct(RoomsRepositoryInterface $rooms)
	{
		$this->rooms = $rooms;
	}

	/**
	 * @param  array{name: string, owner: string, clone?: int}  $params
	 * @return Rooms
	 */
	public function create($params)
	{
		return $this->rooms->create([
			'name' => $params['name'],
			'owner' => $params['owner'],
			'clone' => $params['clone'] ?? 0,
		]);
	}

	/**
	 * @param  array{id: int, owner: string, name?: string, password?: string, cardset?: string, owner_client_id?: string} $params
	 * @return Rooms|null
	 */
	public function update($params)
	{
		return $this->rooms->update($params);
	}

	/**
	 * @param  array{owner: string, id: int}  $params
	 * @return bool
	 */
	public function delete($params)
	{
		return $this->rooms->delete([
			'id' => $params['id'],
			'owner' => $params['owner'],
		]);
	}

	/**
	 * @param  array{owner: string}  $params
	 * @return array<Rooms>
	 */
	public function get($params)
	{
		if (!isset($params['owner'])) {
			return [];
		}

		return $this->rooms->get(['owner' => $params['owner']])->toArray();
	}

	/**
	 * @param  array{owner: string, new_uid: string}  $params
	 * @return boolean
	 */
	public function rebind($params)
	{
		if (!isset($params['owner']) OR !isset($params['new_uid'])) {
			return false;
		}

		$rooms = $this->rooms->get(['owner' => $params['owner']]);

		foreach ($rooms as $key => $room) {
			$room->owner = $params['new_uid'];
			$room->save();
		}

		return true;
	}

}
