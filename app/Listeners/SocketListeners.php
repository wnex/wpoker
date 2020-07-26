<?php
namespace App\Listeners;

use App\Repositories\ClientRepository;
use App\Traits\SocketSendlerMessageTrait;

class SocketListeners {

	use SocketSendlerMessageTrait;

	/** @var ClientRepository */
	protected $repository;

	/**
	 * @param ClientRepository $repository
	 */
	public function __construct(ClientRepository $repository) {
		$this->repository = $repository;
	}

}
