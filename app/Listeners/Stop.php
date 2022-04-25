<?php

namespace App\Listeners;

use App\Listeners\SocketListeners;
use App\Models\Connections;

class Stop extends SocketListeners
{

	/**
	 * Старт воркера
	 * 
	 * @param  \Workerman\Worker $worker
	 * @return void
	 */
	public function handle($worker)
	{
		Connections::query()->delete();
	}

}
