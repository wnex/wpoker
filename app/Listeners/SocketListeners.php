<?php
namespace App\Listeners;

use App\Repositories\ClientsRepository;
use App\Traits\SocketSendlerMessageTrait;

class SocketListeners
{
	use SocketSendlerMessageTrait;

	/** @var ClientsRepository */
	protected $clients;

	/**
	 * @param ClientsRepository $clients
	 */
	public function __construct(ClientsRepository $clients)
	{
		$this->clients = $clients;
	}

}
