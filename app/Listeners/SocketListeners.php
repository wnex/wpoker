<?php
namespace App\Listeners;

use App\Repositories\ConnectionsRepository;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;
use App\Repositories\TasksRepositoryInterface as TasksRepInt;
use Illuminate\Contracts\Events\Dispatcher;
use App\Traits\SocketSendlerMessageTrait;

class SocketListeners
{
	use SocketSendlerMessageTrait;

	/** @var RoomsRepInt */
	protected $rooms;

	/** @var TasksRepInt */
	protected $tasks;

	/** @var Dispatcher */
	protected $event;

	/** @var ConnectionsRepository */
	protected $connects;

	/**
	 * @param ClientsRepository $clients
	 */
	public function __construct(RoomsRepInt $rooms, TasksRepInt $tasks, Dispatcher $event, ConnectionsRepository $connects)
	{
		$this->rooms = $rooms;
		$this->tasks = $tasks;
		$this->event = $event;
		$this->connects = $connects;
	}

}
