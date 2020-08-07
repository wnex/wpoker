<?php
namespace App\Listeners;

use App\Listeners\SocketListeners;
use App\Repositories\ClientsRepository;
use Illuminate\Contracts\Events\Dispatcher;

class WebSocketConnect extends SocketListeners
{
	/** @var Dispatcher */
	private $event;

	public function __construct(Dispatcher $event, ClientsRepository $clients)
	{
		$this->event = $event;

		parent::__construct($clients);
	}

	/**
	 * Открытие соединения
	 * 
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($client_id)
	{
		$this->event->dispatch('server.online.update');
	}

}
