<?php

namespace App\Services;

use GatewayWorker\Lib\Gateway;
use Illuminate\Container\Container;

class SocketRouter {
	/** @var array<string, string> */
	private $routes = [];

	/**
	 * @param  array  $data
	 * @param  string $client_id
	 * @return bool
	 */
	public function routing($data, $client_id) {
		if (isset($data['type']) AND $data['type'] === 'request' AND isset($data['action'])) {
			if (isset($this->routes[$data['action']])) {
				$container = Container::getInstance();
				$response = $container->call($this->routes[$data['action']], ['params' => $data['params'], 'client_id' => $client_id]);
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
	public function add($route, $class) {
		$this->routes[$route] = $class;
	}

	/**
	 * @param  string $response
	 * @param  array  $data
	 * @param  string $client_id
	 * @return void
	 */
	private function sendResponse($response, $data, $client_id) {
		Gateway::sendToAll(json_encode([
			'type' => 'request',
			'action' => $data['action'],
			'request_id' => $data['request_id'],
			'data' => $response,
		]), [$client_id]);
	}

}
