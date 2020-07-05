<?php

namespace App\Services;

use GatewayWorker\Lib\Gateway;
use Illuminate\Container\Container;

class SocketRouter
{
	private $routes = [];
	
	function __construct() {
		
	}

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

	public function add($route, $class) {
		$this->routes[$route] = $class;
	}

	private function sendResponse($response, $data, $client_id) {
		Gateway::sendToAll(json_encode([
			'type' => 'request',
			'action' => $data['action'],
			'request_id' => $data['request_id'],
			'data' => $response,
		]), [$client_id]);
	}

}
