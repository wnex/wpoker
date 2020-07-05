<?php

namespace App\Http\Controllers;

use App\Models\Rooms;
use GatewayWorker\Lib\Gateway;
use Illuminate\Http\Request;

class RoomsController extends Controller {

	public function create(array $params) {
		return Rooms::create([
			'name' => $params['name'],
			'owner' => $params['owner'],
		]);
	}

	public function delete(array $params) {
		return Rooms::where('owner', $params['owner'])->where('id', $params['id'])->delete();
	}

	public function get(array $params) {
		if (!isset($params['owner'])) {
			return [];
		}
		
		$query = Rooms::where('owner', $params['owner']);

		return $query->get();
	}

}
