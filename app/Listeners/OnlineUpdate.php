<?php
namespace App\Listeners;

use App\Models\Connections;
use App\Listeners\SocketListeners;

class OnlineUpdate extends SocketListeners
{
	/**
	 * Обновление онлайна
	 * 
	 * @return void
	 */
	public function handle() {
		$this->sendToAll([
			'action' => 'users.online.update',
			'quantity' => Connections::query()->distinct('uid')->count(),
		]);
	}
}
