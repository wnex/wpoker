<?php

namespace App\Events;

use App\Models\Rooms;
use GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;

class Workerman {
	/**
	 * @var array
	 */
	static $rooms = [];

	/**
	 * @var array
	 */
	static $users = [];

	/**
	 * @param  string $worker
	 * @return void
	 */
	public static function onWorkerStart($worker) {
		Cache::put('users', []);
		Cache::put('rooms', []);
	}

	/**
	 * @param  string $client_id
	 * @return void
	 */
	public static function onConnect($client_id) {

	}

	/**
	 * @param  string $client_id
	 * @param  array  $data
	 * @return void
	 */
	public static function onWebSocketConnect($client_id, $data) {
	}

	/**
	 * @return array
	 */
	public static function getUsers() {
		return Cache::get('users', []);
	}

	/**
	 * @param  string $client_id
	 * @return array
	 */
	public static function getUser($client_id) {
		return Arr::get(self::getUsers(), $client_id, []);
	}

	/**
	 * @param  string $client_id
	 * @param  array  $data
	 * @return array
	 */
	public static function setUser($client_id, $data) {
		$users = self::getUsers();
		$user = self::getUser($client_id);
		$user = array_merge($user, $data);
		Arr::set($users, $client_id, $user);
		Cache::put('users', $users);
		return $user;
	}

	/**
	 * @param  string $client_id
	 * @return void
	 */
	public static function removeUser($client_id) {
		$users = self::getUsers();
		unset($users[$client_id]);
		Cache::put('users', $users);
	}

	/**
	 * @param  string $client_id
	 * @param  string $data
	 * @return void
	 */
	public static function onMessage($client_id, $data) {
		$data = json_decode($data, true);

		if (isset($data['type']) AND $data['type'] === 'ping') {
			return;
		}

		if (app('App\Services\SocketRouter')->routing($data, $client_id)) {
			return;
		}

		event("socket.{$data['action']}", [$data, $client_id]);
	}

	/**
	 * @param  string $client_id
	 * @return void
	 */
	public static function onClose($client_id) {
		event("socket.close", [$client_id]);
	}

	/**
	 * @param  string $room
	 * @param  bool $withVote
	 * @return array
	 */
	public static function getAllUsers($room, $withVote = false) {
		$roomModel = Rooms::where('hash', $room)->first();

		if (is_null($roomModel)) {
			return [];
		}

		$users = [];
		foreach (Rooms::getUsers($room) as $user_id) {
			$user = self::getUser($user_id);
			$u = [
				'id' => $user_id,
				'name' => $user['name'],
				'isVoted' => isset($user['isVoted']) ? $user['isVoted'] : false,
				'isOwner' => $roomModel->isOwner($user['user']),
			];

			if ($withVote AND isset($user['vote'])) {
				$u['vote'] = $user['vote'];
			}

			$users[] = $u;
		}

		return $users;
	}

	/**
	 * @param  string $owner
	 * @return string
	 */
	public static function getOwnerId($owner) {
		foreach (self::getUsers() as $key => $user) {
			if ($user['user'] === $owner) {
				return $user['id'];
			}
		}
	}
}
