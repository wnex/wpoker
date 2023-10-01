<?php
namespace App\Listeners;

use App\Models\Connections;
use App\Listeners\SocketListeners;

class Close extends SocketListeners
{
	protected $secondsForReconnect = 60;
	/**
	 * Закрытие соединения
	 * 
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($client_id)
	{
		$rooms = Connections::where('id', $client_id)->distinct(['uid', 'room_id'])->pluck('room_id');
		$users = Connections::whereIn('room_id', $rooms)->pluck('id');
		$connect = Connections::where('id', $client_id)->first();

		if (!empty($connect->room_id) AND $this->secondsForReconnect > 0) {
			$timer_id = $this->addTimer($this->secondsForReconnect, function() use($client_id, $rooms, $users) {
				$connect = Connections::where('id', $client_id)->first();

				$this->deleteConnection($connect, $rooms, $client_id);

				Connections::where('id', $client_id)->update([
					'active' => false,
				]);
				$this->sendToAll([
					'action' => 'room.user.disconnected',
					'id' => $client_id,
				], $users->toArray(), [$client_id]);
			}, [], false);
		} else {
			$this->deleteConnection($connect, $rooms, $client_id);
		}

		$this->event->dispatch('server.online.update');
	}

	protected function deleteConnection($connect, $rooms, $client_id)
	{
		if (isset($connect)) {
			$connect->delete();
			$users = Connections::whereIn('room_id', $rooms)->pluck('id');

			$this->sendToAll([
				'action' => 'room.left.user',
				'id' => $client_id,
			], $users->toArray(), [$client_id]);

			foreach ($rooms as $room_id) {
				$this->event->dispatch('server.room.vote.finish', [$room_id]);
			}
		}
	}

}
