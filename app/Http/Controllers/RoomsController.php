<?php

namespace App\Http\Controllers;

use App\Models\Rooms;
use Illuminate\Http\Request;

class RoomsController extends Controller {

	/**
	 * @param  array{name: string, owner: string}  $params
	 * @return Rooms
	 */
	public function create($params) {
		return Rooms::create([
			'name' => $params['name'],
			'owner' => $params['owner'],
		]);
	}

	/**
	 * @param  array{owner: string, id: int}  $params
	 * @return bool
	 */
	public function delete($params) {
		return Rooms::where('owner', $params['owner'])->where('id', $params['id'])->delete();
	}

	/**
	 * @param  array{owner: string}  $params
	 * @return array<Rooms>
	 */
	public function get($params) {
		if (!isset($params['owner'])) {
			return [];
		}

		$query = Rooms::where('owner', $params['owner']);

		return $query->get()->toArray();
	}

}
