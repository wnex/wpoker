<?php
namespace App\Repositories;

use App\Models\Rooms;
use App\Models\Tasks;
use App\Models\Connections;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Events\Dispatcher;

class RoomsRepository implements RoomsRepositoryInterface {
	/** @var Rooms */
	private $rooms;

	/** @var Dispatcher */
	private $event;

	/**
	 * @param Rooms $rooms
	 * @param Dispatcher $event
	 */
	public function __construct(Rooms $rooms, Dispatcher $event)
	{
		$this->rooms = $rooms;
		$this->event = $event;
	}

	/**
	 * @param  array{name: string, owner: string, clone: int} $params
	 * @return Rooms
	 */
	public function create($params)
	{
		if ($params['clone'] !== 0) {
			$clone = $this->rooms->query()->whereId($params['clone'])->firstOrNew();
			return $this->rooms->query()->create(array_merge($params, [
				'cardset' => $clone->cardset,
				'password' => $clone->password,
			]));
		} else {
			return $this->rooms->query()->create($params);
		}
	}

	/**
	 * @param  array{id: int, owner: string, name?: string, password?: string, cardset?: string} $params
	 * @return Rooms|null
	 */
	public function update($params)
	{
		$room = $this->first(['id' => $params['id']]);

		if (is_null($room)) {
			return null;
		}

		if ($room->isOwner($params['owner'])) {
			if (isset($params['name'])) {
				$room->name = $params['name'];
			}

			if (isset($params['password'])) {
				$room->password = $params['password'];
			}

			if (isset($params['cardset'])) {
				$room->cardset = $params['cardset'];
			}

			$room->save();

			$this->sendToRoom($room->hash, [
				'action' => 'room.update',
				'name' => $room->name,
				'cardset' => $room->cardset,
				'hasPassword' => $room->hasPassword,
			]);

			return $room;
		}
	}

	/**
	 * @param  array{id?: int, owner?: string} $conditions
	 * @return mixed
	 */
	public function delete($conditions)
	{
		$query = $this->rooms->query();

		if (empty($conditions)) {
			throw new \Exception("Empty conditions");
		}

		foreach ($conditions as $field => $value) {
			$query->where($field, $value);
		}

		return $query->delete();
	}

	/**
	 * @param  array{id?: int, owner?: string} $conditions
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function get($conditions)
	{
		$query = $this->rooms->query();

		foreach ($conditions as $field => $value) {
			$query->where($field, $value);
		}

		return $query->orderBy('updated_at', 'desc')->get();
	}

	/**
	 * @param  array{id?: int, owner?: string, hash?: string} $conditions
	 * @return Rooms|null
	 */
	public function first($conditions)
	{
		$query = $this->rooms->query();

		foreach ($conditions as $field => $value) {
			$query->where($field, $value);
		}

		return $query->first();
	}

	/**
	 * @param  Rooms $room
	 * @return Tasks|null
	 */
	public function getLastTask($room)
	{
		return $room->tasks()->orderBy('order', 'desc')->first();
	}

	/**
	 * @param  string $hash
	 * @param  array  $params
	 * @param  array  $exclude
	 * @return void
	 */
	public function sendToRoom($hash, $params, $exclude = [])
	{
		$connects = Connections::where('room_id', $hash)->distinct('uid')->pluck('id');
		$this->event->dispatch('server.socket.sendToAll', [$params, $connects->toArray(), $exclude]);
	}

}
