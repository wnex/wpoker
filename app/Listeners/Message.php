<?php

namespace App\Listeners;

use App\Listeners\SocketListeners;
use App\Models\Rooms;
use App\Repositories\ClientRepository;
use App\Services\SocketRouterContract as SocketRouter;
use Illuminate\Events\Dispatcher;

class Message extends SocketListeners {

	/** @var array{type?: string, action?: string} */
	private $data = [];

	/** @var SocketRouter */
	private $router;

	/** @var Dispatcher */
	private $event;

	/**
	 * @param SocketRouter $router
	 * @param Dispatcher   $event
	 */
	public function __construct(SocketRouter $router, Dispatcher $event, ClientRepository $repository) {
		$this->router = $router;
		$this->event = $event;

		parent::__construct($repository);
	}

	/**
	 * Обработка сообщений
	 * 
	 * @param  string $client_id
	 * @param  string $dataJson
	 * @return void
	 */
	public function handle($client_id, $dataJson) {
		$this->data = $this->parseData($dataJson);

		if ($this->isPingPong()) {
			return;
		}

		if ($this->isRequest()) {
			$this->router->routing($this->data, $client_id);
			return;
		}

		if ($this->isEvent()) {
			$this->event->dispatch($this->getEventName(), [$this->data, $client_id]);
		}
	}

	/**
	 * @param  string $data
	 * @return array{type?: string, action?: string}
	 */
	private function parseData($data) {
		return json_decode($data, true);
	}

	/**
	 * @return boolean
	 */
	private function isPingPong() {
		return isset($this->data['type']) AND $this->data['type'] === 'ping';
	}

	/**
	 * @return boolean
	 */
	private function isRequest() {
		return isset($this->data['type']) AND $this->data['type'] === 'request';
	}

	/**
	 * @return boolean
	 */
	private function isEvent() {
		return isset($this->data['action']);
	}

	/**
	 * @return string
	 */
	private function getEventName() {
		return isset($this->data['action']) ? "socket.{$this->data['action']}" : "socket.";
	}

}
