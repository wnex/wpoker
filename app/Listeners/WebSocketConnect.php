<?php
namespace App\Listeners;

use GatewayWorker\Lib\Gateway;
use App\Listeners\SocketListeners;

class WebSocketConnect extends SocketListeners {

	/**
	 * Открытие соединения
	 * 
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($client_id) {
		event('server.online.update');
	}

}
