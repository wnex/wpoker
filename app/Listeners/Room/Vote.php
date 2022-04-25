<?php
namespace App\Listeners\Room;

use App\Models\Connections;
use App\Listeners\SocketListeners;

class Vote extends SocketListeners
{
	/**
	 * Оценка
	 * 
	 * @param  array{room: string, point: string, view: string}  $data
	 * @param  string $client_id
	 * @return void
	 */
	public function handle($data, $client_id)
	{
		$connect = Connections::where('id', $client_id)->first();
		$connect->vote = array_merge($connect->vote, [
			'is_voted' => true,
			'value' => $data['point'],
			'view' => $data['view'],
		]);
		$connect->save();

		$this->rooms->sendToRoom($data['room'], [
			'action' => 'room.vote',
			'id' => $client_id,
		]);

		$this->sendToCurrentClient([
			'action' => 'room.vote.you',
			'id' => $client_id,
			'vote' => $data['point'],
			'voteView' => $data['view'],
		]);

		$this->event->dispatch('server.room.vote.finish', [$data['room']]);
	}

}
