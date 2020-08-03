<?php
namespace App\Listeners;

use App\Repositories\ClientsRepository;
use App\Traits\SocketSendlerMessageTrait;

class SocketListeners {

	use SocketSendlerMessageTrait;

	/** @var ClientsRepository */
	protected $repository;

	/**
	 * @param ClientsRepository $repository
	 */
	public function __construct(ClientsRepository $repository) {
		$this->repository = $repository;
	}

}
