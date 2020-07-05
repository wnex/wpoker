<?php

namespace App\Events;

use App\Models\Rooms;
use GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;

class Workerman {
	static $rooms = [];
	static $users = [];

	public static function onWorkerStart($worker) {

	}

	public static function onConnect($client_id) {

	}

	public static function onWebSocketConnect($client_id, $data) {
	}

	public static function onMessage($client_id, $data) {
		$data = json_decode($data, true);

		if (app('App\Services\SocketRouter')->routing($data, $client_id)) {
			return false;
		}

		/*if (isset($data['type']) AND $data['type'] === 'request' AND isset($data['request_id'])) {
			Gateway::sendToCurrentClient(json_encode([
				'type' => 'request',
				'request_id' => $data['request_id'],
				'ping' => 'pong',
			]));
			return false;
		}*/

		// Вход в комнату [room, name, user]
		if ($data['action'] === 'room.open') {
			self::$users[$client_id]['id'] = $client_id;
			self::$users[$client_id]['room'] = $data['room'];
			self::$users[$client_id]['name'] = $data['name'];
			self::$users[$client_id]['user'] = $data['user'];

			self::$rooms[$data['room']][] = $client_id;

			$users_in_room = self::$rooms[$data['room']];
			Gateway::sendToAll(json_encode([
				'action' => 'room.entered.user',
				'id' => $client_id,
				'name' => $data['name'],
			]), $users_in_room, [$client_id]);

			$room = Rooms::where('hash', $data['room'])->first();

			Gateway::sendToCurrentClient(json_encode([
				'action' => 'room.parameters',
				'id' => $client_id,
				'users' => self::getAllUsers($data['room']),
				'owner' => self::getOwnerId($room->owner),
				'stage' => $room->stage,
			]));
		}

		// Смена имени [room, name]
		if ($data['action'] === 'room.user.changeName') {
			self::$users[$client_id]['name'] = $data['name'];

			$users_in_room = self::$rooms[$data['room']];
			Gateway::sendToAll(json_encode([
				'action' => 'room.user.changeName',
				'id' => $client_id,
				'name' => $data['name'],
			]), $users_in_room);
		}

		// Старт голосования [room]
		if ($data['action'] === 'room.vote.start') {
			$room = Rooms::where('hash', $data['room'])->first();
			$room->stage = 1;
			$room->save();

			$users_in_room = self::$rooms[$data['room']];
			Gateway::sendToAll(json_encode([
				'action' => 'room.vote.start',
			]), $users_in_room);
		}

		// Оценка [room, vote]
		if ($data['action'] === 'room.vote') {
			self::$users[$client_id]['isVoted'] = true;
			self::$users[$client_id]['vote'] = $data['vote'];

			$users_in_room = self::$rooms[$data['room']];
			Gateway::sendToAll(json_encode([
				'action' => 'room.vote',
				'id' => $client_id,
			]), $users_in_room);

			Gateway::sendToCurrentClient(json_encode([
				'action' => 'room.vote.you',
				'id' => $client_id,
				'vote' => $data['vote'],
			]));

			$voted = 0;
			foreach ($users_in_room as $user_id) {
				if (isset(self::$users[$user_id]['vote'])) {
					$voted++;
				}
			}

			// Все проголосовали
			if ($voted === count($users_in_room)) {
				Gateway::sendToAll(json_encode([
					'action' => 'room.vote.final',
					'users' => self::getAllUsers($data['room'], true),
				]), $users_in_room);
			}
		}

		// Сброс оценок [room]
		if ($data['action'] === 'room.vote.reset') {
			$room = Rooms::where('hash', $data['room'])->first();
			$room->stage = 0;
			$room->save();

			$users_in_room = self::$rooms[$data['room']];
			foreach ($users_in_room as $user_id) {
				if (isset(self::$users[$user_id]['vote'])) {
					unset(self::$users[$user_id]['isVoted']);
					unset(self::$users[$user_id]['vote']);
				}
			}
			Gateway::sendToAll(json_encode([
				'action' => 'room.vote.reset',
			]), $users_in_room);
		}


		// Пасхалки [room, point]
		if ($data['action'] === 'room.eggs.shake') {
			$users_in_room = self::$rooms[$data['room']];
			Gateway::sendToAll(json_encode([
				'action' => 'room.eggs.shake',
				'point' => $data['point'],
			]), $users_in_room);
		}

	}

	public static function onClose($client_id) {
		if(isset(self::$users[$client_id])) {
			$room = self::$users[$client_id]['room'];
			if (in_array($client_id, self::$rooms[$room])) {
				unset(self::$rooms[$room][array_search($client_id, self::$rooms[$room])]);
			}

			unset(self::$users[$client_id]);
		}

		Gateway::sendToAll(json_encode([
			'action' => 'room.left.user',
			'id' => $client_id,
		]));
	}


	private static function getAllUsers($room, $withVote = false) {
		$users = [];
		foreach (self::$rooms[$room] as $user_id) {
			$user = self::$users[$user_id];
			$u = [
				'id' => $user_id,
				'name' => $user['name'],
				'isVoted' => isset($user['isVoted']) ? $user['isVoted'] : false,
			];

			if ($withVote) {
				$u['vote'] = $user['vote'];
			}

			$users[] = $u;
		}

		return $users;
	}

	private static function getOwnerId($owner) {
		foreach (self::$users as $key => $user) {
			if ($user['user'] === $owner) {
				return $user['id'];
			}
		}
	}
}
