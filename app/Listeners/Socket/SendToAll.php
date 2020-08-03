<?php

namespace App\Listeners\Socket;

use App\Listeners\SocketListeners;
use App\Models\Rooms;

class SendToAll extends SocketListeners {

	/**
	 * Закрытие соединения
	 * 
	 * @param  array                        $data
	 * @param  array<array-key, mixed>|null $client_id_array
	 * @param  array<array-key, mixed>|null $exclude_client_id
	 * @return void
	 */
	public function handle($data, $client_id_array = null, $exclude_client_id = null) {
		$this->sendToAll($data, $client_id_array, $exclude_client_id);
	}

}
