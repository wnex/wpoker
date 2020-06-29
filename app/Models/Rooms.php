<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rooms extends Model {

	protected $fillable = ['name', 'owner'];

	protected static function boot() {
		parent::boot();

		static::creating(function ($query) {
			$query->hash = mb_strimwidth(hash('sha256', time() . rand()), 2, 10);
		});
	}
}
