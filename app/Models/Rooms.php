<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Rooms extends Model {

	protected $fillable = ['name', 'owner', 'active_task_id'];

	protected static function boot() {
		parent::boot();

		static::creating(function ($query) {
			$query->hash = mb_strimwidth(hash('sha256', time() . rand()), 2, 10);
		});
	}

	public function tasks() {
		return $this->hasMany('App\Models\Tasks', 'room_id');
	}

	public function activeTask() {
		return $this->hasOne('App\Models\Tasks', 'id', 'active_task_id');
	}

	public function getNextTask() {
		return $this->tasks()->whereNull('story_point')->orderBy('order', 'asc')->first();
	}

	public function haveActiveStage() {
		return $this->stage === 1 OR $this->stage === 2;
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
