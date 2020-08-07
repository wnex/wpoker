<?php
namespace App\Repositories;

use App\Models\Rooms;
use Illuminate\Support\Arr;
use App\Services\WebSocketData;
use App\Repositories\RoomsRepositoryInterface as RoomsRepInt;

class ClientsRepository {
	/** @var WebSocketData */
	private $storage;

	/** @var RoomsRepInt */
	private $rooms;

	/**
	 * @param WebSocketData $storage
	 */
	public function __construct(WebSocketData $storage, RoomsRepInt $rooms)
	{
		$this->storage = $storage;
		$this->rooms = $rooms;
	}

	/**
	 * @return array
	 */
	public function getUsers()
	{
		return $this->storage->get('users', []);
	}

	/**
	 * @param  string $client_id
	 * @return array
	 */
	public function getUser($client_id)
	{
		return Arr::get($this->getUsers(), $client_id, []);
	}

	/**
	 * @param  string $client_id
	 * @param  array  $data
	 * @return array
	 */
	public function setUser($client_id, $data)
	{
		$users = $this->getUsers();
		$user = $this->getUser($client_id);
		$user = array_merge($user, $data);
		Arr::set($users, $client_id, $user);
		$this->storage->put('users', $users);
		return $user;
	}

	/**
	 * @param  string $client_id
	 * @return void
	 */
	public function removeUser($client_id)
	{
		$users = $this->getUsers();
		unset($users[$client_id]);
		$this->storage->put('users', $users);
	}

	/**
	 * @param  string $room
	 * @param  bool $withVote
	 * @return array
	 */
	public function getAllUsers($room, $withVote = false)
	{
		$roomModel = $this->rooms->first(['hash' => $room]);

		if (is_null($roomModel)) {
			return [];
		}

		$users = [];
		foreach ($this->rooms->getClientsFromRoom($room) as $client_id) {
			$client = $this->getUser($client_id);
			$user = [
				'id' => $client_id,
				'name' => $client['name'],
				'isVoted' => isset($client['isVoted']) ? $client['isVoted'] : false,
				'isOwner' => $roomModel->isOwner($client['user']),
			];

			if ($withVote AND isset($client['vote'])) {
				$user['vote'] = $client['vote'];
				$user['voteView'] = $client['voteView'];
			}

			$users[] = $user;
		}

		return $users;
	}

	/**
	 * @param  string $owner
	 * @return string
	 */
	public function getOwnerId($owner)
	{
		foreach ($this->getUsers() as $key => $user) {
			if ($user['user'] === $owner) {
				return $user['id'];
			}
		}
	}

}
