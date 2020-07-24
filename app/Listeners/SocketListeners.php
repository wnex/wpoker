<?php

namespace App\Listeners;

use GatewayWorker\Lib\Gateway;

class SocketListeners {

	/**
	 * @param  array                        $data
	 * @param  array<array-key, mixed>|null $client_id_array
	 * @param  array<array-key, mixed>|null $exclude_client_id
	 * @return null
	 */
	public function sendToAll($data, $client_id_array = null, $exclude_client_id = null) {
		return Gateway::sendToAll(json_encode($data), $client_id_array, $exclude_client_id);
	}

	/**
	 * @param  array  $data
	 * @return bool
	 */
	public function sendToCurrentClient($data) {
		return Gateway::sendToCurrentClient(json_encode($data));
	}

}
