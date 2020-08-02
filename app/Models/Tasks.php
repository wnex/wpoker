<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Tasks
 *
 * @property int $id
 * @property string $text
 * @property int $order
 * @property int $room_id
 * @property string|null $story_point
 * @property string|null $story_point_view
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Rooms $room
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tasks newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tasks newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tasks query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tasks whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tasks whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tasks whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tasks whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tasks whereStoryPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tasks whereStoryPointView($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tasks whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tasks whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Tasks extends Model {

	/** @var array<string> */
	protected $fillable = ['text', 'order', 'story_point', 'story_point_view', 'room_id'];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function room() {
		return $this->belongsTo(Rooms::class, 'room_id');
	}

}
