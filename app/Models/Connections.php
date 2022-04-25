<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Connections
 *
 * @property string $id
 * @property string $uid
 * @property string $room_id
 * @property string $name
 * @property string $vote
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Connections extends Model {

	/** @var array */
	protected $fillable = ['id', 'uid', 'room_id', 'name', 'active', 'vote'];

	/** @var array */
	protected $casts = [
		'vote' => 'array',
	];

	protected $keyType = 'string';

	/**
	 * @return void
	 */
	protected static function boot() {
		parent::boot();

		static::creating(function (Model $query)
		{
			$query->vote = [
				'is_voted' => false,
				'has_vote' => true,
				'value' => null,
				'view' => null,
			];
		});
	}

}
