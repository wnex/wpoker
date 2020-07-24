<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Tasks extends Model {

	/** @var array<string> */
	protected $fillable = ['text', 'order', 'story_point', 'room_id'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function room() {
		return $this->belongsTo(Rooms::class, 'room_id');
	}

}
