<?php
namespace App\Listeners\Room;

use App\Models\Connections;
use App\Listeners\SocketListeners;
use App\Repositories\ClientsRepository;

class UserChangeName extends SocketListeners
{
	/**
	 * Смена имени
	 * 
	 * @param  array{room: string, name: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		Connections::where('id', $client_id)->update(['name' => $data['name']]);

		$this->rooms->sendToRoom($data['room'], [
			'action' => 'room.user.changeName',
			'id' => $client_id,
			'name' => $data['name'],
		]);
	}

}
