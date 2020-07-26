<?php

namespace App\Listeners;

use App\Listeners\SocketListeners;
use Illuminate\Support\Facades\Cache;

class Start extends SocketListeners {

	/**
	 * Старт воркера
	 * 
	 * @param  \Workerman\Worker $worker
	 * @return void
	 */
	public function handle($worker) {
		Cache::put('users', []);
		Cache::put('rooms', []);
	}

}
