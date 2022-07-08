<?php
namespace App\Listeners;

use App\Models\Connections;

class UserConnect extends SocketListeners
{
	/**
	 * Получение данных о подключившемся пользователе
	 * 
	 * @param  array{name: string, uid: string} $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id) {
		$connect = new Connections;
		$connect->id = $client_id;
		$connect->name = $data['name'];
		$connect->uid = $data['uid'];
		$connect->save();

		$this->bindUid($client_id, $data['uid']);

		$this->event->dispatch('server.online.update');
	}

}
