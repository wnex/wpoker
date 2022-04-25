<?php
namespace App\Listeners;

use App\Listeners\SocketListeners;
use App\Repositories\ClientsRepository;

class WebSocketConnect extends SocketListeners
{
	/**
	 * Открытие соединения
	 * 
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($client_id)
	{
		
	}

}
