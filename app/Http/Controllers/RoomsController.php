<?php

namespace App\Http\Controllers;

use App\Models\Rooms;
use Illuminate\Http\Request;

class RoomsController extends Controller {

	public function create(Request $request) {
		return response()->json(Rooms::create([
			'name' => $request->json('name'),
			'owner' => $request->json('owner'),
		]));
	}

	public function get(Request $request) {
		$query = Rooms::query();

		if ($owner = $request->input('owner')) {
			$query->where('owner', $owner);
		}

		return response()->json($query->get());
	}

}
