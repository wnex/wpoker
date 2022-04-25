<?php

namespace App\Listeners;

use App\Listeners\SocketListeners;

class Start extends SocketListeners
{

	/**
	 * Старт воркера
	 * 
	 * @param  \Workerman\Worker $worker
	 * @return void
	 */
	public function handle($worker)
	{
		
	}

}
