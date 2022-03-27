<?php

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

Route::get('/{any}', function (Request $request) {
	$response = view('app');

	if (!$request->cookie('uid', false)) {
		if ($request->cookie('user', false)) {
			$uid = $request->cookie('user', false);
			\Cookie::queue(\Cookie::forget('user'));
		} else {
			$uid = trim(file_get_contents('/proc/sys/kernel/random/uuid'));
		}
		\Cookie::queue(\Cookie::make('uid', $uid, 10000000, null, null, null, false));
	}

	return $response;
})->where('any', '.*');
