<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Rooms extends Model {

	protected $fillable = ['name', 'owner'];

	protected static function boot() {
		parent::boot();

		static::creating(function ($query) {
			$query->hash = mb_strimwidth(hash('sha256', time() . rand()), 2, 10);
		});
	}

	public function tasks() {
		return $this->hasMany('App\Models\Tasks');
	}

	public static function getAllRooms() {
		return Cache::get('rooms', []);
	}

	public static function getUsers($hash) {
		return Arr::get(self::getAllRooms(), $hash, []);
	}

	public static function addUser($hash, $client_id) {
		if (!in_array($client_id, self::getUsers($hash))) {
			$rooms = self::getAllRooms();
			$room = self::getUsers($hash);
			$room[] = $client_id;
			Arr::set($rooms, $hash, $room);
			Cache::put('rooms', $rooms);
		}
	}

	public static function removeUser($hash, $client_id) {
		if (in_array($client_id, self::getUsers($hash))) {
			$rooms = self::getAllRooms();
			$room = self::getUsers($hash);
			unset($room[array_search($client_id, $room)]);
			Arr::set($rooms, $hash, $room);
			Cache::put('rooms', $rooms);
		}
	}
}
