<?php
namespace App\Repositories;

use App\Models\Rooms;
use Illuminate\Support\Arr;
use App\Services\WebSocketData;

class ClientsRepository {

	/** @var WebSocketData */
	private $storage;

	/**
	 * @param WebSocketData $storage
	 */
	public function __construct(WebSocketData $storage) {
		$this->storage = $storage;
	}

	/**
	 * @return array
	 */
	public function getUsers() {
		return $this->storage->get('users', []);
	}

	/**
	 * @param  string $client_id
	 * @return array
	 */
	public function getUser($client_id) {
		return Arr::get($this->getUsers(), $client_id, []);
	}

	/**
	 * @param  string $client_id
	 * @param  array  $data
	 * @return array
	 */
	public function setUser($client_id, $data) {
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
	public function removeUser($client_id) {
		$users = $this->getUsers();
		unset($users[$client_id]);
		$this->storage->put('users', $users);
	}

	/**
	 * @param  string $room
	 * @param  bool $withVote
	 * @return array
	 */
	public function getAllUsers($room, $withVote = false) {
		$roomModel = Rooms::where('hash', $room)->first();

		if (is_null($roomModel)) {
			return [];
		}

		$users = [];
		foreach (Rooms::getUsers($room) as $user_id) {
			$user = $this->getUser($user_id);
			$u = [
				'id' => $user_id,
				'name' => $user['name'],
				'isVoted' => isset($user['isVoted']) ? $user['isVoted'] : false,
				'isOwner' => $roomModel->isOwner($user['user']),
			];

			if ($withVote AND isset($user['vote'])) {
				$u['vote'] = $user['vote'];
				$u['voteView'] = $user['voteView'];
			}

			$users[] = $u;
		}

		return $users;
	}

	/**
	 * @param  string $owner
	 * @return string
	 */
	public function getOwnerId($owner) {
		foreach ($this->getUsers() as $key => $user) {
			if ($user['user'] === $owner) {
				return $user['id'];
			}
		}
	}

}
