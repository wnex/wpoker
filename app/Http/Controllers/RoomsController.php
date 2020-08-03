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
	 * @param  array{name: string, owner: string}  $params
	 * @return Rooms
	 */
	public function create($params)
	{
		return $this->rooms->create([
			'name' => $params['name'],
			'owner' => $params['owner'],
		]);
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

}
