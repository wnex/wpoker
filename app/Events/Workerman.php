<?php

namespace App\Events;

use App\Models\Rooms;
use GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;

class Workerman {
	static $rooms = [];
	static $users = [];

	public static function onWorkerStart($worker) {
		Cache::put('users', []);
		Cache::put('rooms', []);
	}

	public static function onConnect($client_id) {

	}

	public static function onWebSocketConnect($client_id, $data) {
	}

	public static function getUsers() {
		return Cache::get('users', []);
	}

	public static function getUser($client_id) {
		return Arr::get(self::getUsers(), $client_id, []);
	}

	public static function setUser($client_id, $data) {
		$users = self::getUsers();
		$user = self::getUser($client_id);
		$user = array_merge($user, $data);
		Arr::set($users, $client_id, $user);
		Cache::put('users', $users);
		return $user;
	}

	public static function removeUser($client_id) {
		$users = self::getUsers();
		unset($users[$client_id]);
		Cache::put('users', $users);
	}


	public static function onMessage($client_id, $data) {
		$data = json_decode($data, true);

		if (app('App\Services\SocketRouter')->routing($data, $client_id)) {
			return false;
		}

		event("socket.{$data['action']}", [$data, $client_id]);
	}

	public static function onClose($client_id) {
		event("socket.close", [$client_id]);
	}


	public static function getAllUsers($room, $withVote = false) {
		$roomModel = Rooms::where('hash', $room)->first();
		$users = [];
		foreach (Rooms::getUsers($room) as $user_id) {
			$user = self::getUser($user_id);
			$u = [
				'id' => $user_id,
				'name' => $user['name'],
				'isVoted' => isset($user['isVoted']) ? $user['isVoted'] : false,
				'isOwner' => $user['user'] === $roomModel->owner,
			];

			if ($withVote) {
				$u['vote'] = $user['vote'];
			}

			$users[] = $u;
		}

		return $users;
	}

	public static function getOwnerId($owner) {
		foreach (self::getUsers() as $key => $user) {
			if ($user['user'] === $owner) {
				return $user['id'];
			}
		}
	}
}
