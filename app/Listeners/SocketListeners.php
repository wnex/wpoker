<?php

namespace App\Listeners;

use GatewayWorker\Lib\Gateway;

class SocketListeners {

	public function sendToAll($data, $client_id_array = null, $exclude_client_id = null) {
		return Gateway::sendToAll(json_encode($data), $client_id_array, $exclude_client_id);
	}

	public function sendToCurrentClient($data) {
		return Gateway::sendToCurrentClient(json_encode($data));
	}

}
