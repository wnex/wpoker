<?php
namespace App\Services;

use GatewayWorker\Lib\Gateway;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Container\Container;

class SocketRouter implements SocketRouterContract
{
	/** @var array<string, string> */
	private $routes = [];

	/** @var Container */
	private $container;

	/** @var Dispatcher */
	private $event;

	public function __construct(Container $container, Dispatcher $event)
	{
		$this->container = $container;
		$this->event = $event;
	}

	/**
	 * @param  array  $data
	 * @param  string $client_id
	 * @return bool
	 */
	public function routing($data, $client_id)
	{
		if (isset($data['type']) AND $data['type'] === 'request' AND isset($data['action'])) {
			if (isset($this->routes[$data['action']])) {
				$response = $this->container->call($this->routes[$data['action']], [
					'params' => $data['params'],
					'client_id' => $client_id,
				]);

				$this->sendResponse($response, $data, $client_id);
				return true;
			}
		}

		return false;
	}

	/**
	 * @param  string $route
	 * @param  string $class
	 * @return void
	 */
	public function add($route, $class)
	{
		$this->routes[$route] = $class;
	}

	/**
	 * @return array<string, string>
	 */
	public function all()
	{
		return $this->routes;
	}

	/**
	 * @param  string $response
	 * @param  array  $data
	 * @param  string $client_id
	 * @return void
	 */
	private function sendResponse($response, $data, $client_id)
	{
		$this->event->dispatch('server.socket.sendToAll', [[
			'type' => 'request',
			'action' => $data['action'],
			'request_id' => $data['request_id'],
			'data' => $response,
		], [$client_id]]);
	}

}
