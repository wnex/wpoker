<?php

use App\Models\Rooms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$meta = function($name = '') {
	$array = [
		'title' => config('app.name').' - '.config('app.tagline'),
		'image' => asset('/images/logo_og.png'),
		'raw' => [
			'name' => config('app.name'),
			'tagline' => config('app.tagline'),
		],
	];

	if (!empty($name)) {
		$array['title'] = $name.' - '.$array['raw']['name'];
	}

	return $array;
};

Route::get('/room/{rooms:hash}', function(Rooms $rooms) use($meta) {
	return view('app', ['meta' => $meta($rooms->name)]);
});

Route::get('/{any}', function(Request $request) use($meta) {
	return view('app', ['meta' => $meta()]);
})->where('any', '.*');
