<?php
namespace App\Listeners;

use GatewayWorker\Lib\Gateway;
use App\Listeners\SocketListeners;

class OnlineUpdate extends SocketListeners {

	/**
	 * Обновление онлайна
	 * 
	 * @return void
	 */
	public function handle() {
		$this->sendToAll([
			'action' => 'users.online.update',
			'quantity' => Gateway::getAllClientIdCount(),
		]);
	}

}
