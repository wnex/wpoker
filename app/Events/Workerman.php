<?php

namespace App\Events;

class Workerman {

	/**
	 * @param  \Workerman\Worker $worker
	 * @return void
	 */
	public static function onWorkerStart($worker) {
		event("server.socket.start", [$worker]);
	}

	/**
	 * @param  string $client_id
	 * @return void
	 */
	public static function onConnect($client_id) {
		event("server.socket.connect", [$client_id]);
	}

	/**
	 * @param  string $client_id
	 * @param  array  $data
	 * @return void
	 */
	public static function onWebSocketConnect($client_id, $data) {
		event("server.socket.webSocketConnect", [$client_id, $data]);
	}

	/**
	 * @param  string $client_id
	 * @param  string $data
	 * @return void
	 */
	public static function onMessage($client_id, $data) {
		event("server.socket.message", [$client_id, $data]);
	}

	/**
	 * @param  string $client_id
	 * @return void
	 */
	public static function onClose($client_id) {
		event("server.socket.close", [$client_id]);
	}

	/**
	 * @param  \Workerman\Worker $worker
	 * @return void
	 */
	public static function onWorkerStop($worker) {
		event("server.socket.stop", [$worker]);
	}
	
}
