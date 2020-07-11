<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Tasks extends Model {

	protected $fillable = ['text', 'order', 'story_point', 'rooms_id'];

	public function room() {
		return $this->belongsTo('App\Models\Rooms', 'rooms_id');
	}

}
