<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Rooms
 *
 * @property int $id
 * @property string $name
 * @property string $hash
 * @property int $stage
 * @property string $owner
 * @property int|null $active_task_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Tasks|null $activeTask
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tasks[] $tasks
 * @property-read int|null $tasks_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Rooms newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Rooms newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Rooms query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Rooms whereActiveTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Rooms whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Rooms whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Rooms whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Rooms whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Rooms whereOwner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Rooms whereStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Rooms whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Rooms extends Model {

	/** @var array<string> */
	protected $fillable = ['name', 'owner', 'active_task_id'];

	/**
	 * @return void
	 */
	protected static function boot() {
		parent::boot();

		static::creating(function (Model $query) {
			$query->hash = mb_strimwidth(hash('sha256', (string)time() . (string)rand()), 2, 10);
		});
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function tasks() {
		return $this->hasMany(Tasks::class, 'room_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function activeTask() {
		return $this->hasOne(Tasks::class, 'id', 'active_task_id');
	}

	/**
	 * @return Tasks|null
	 */
	public function getNextTask() {
		return $this->tasks()->whereNull('story_point')->orderBy('order', 'asc')->first();
	}

	/**
	 * @return bool
	 */
	public function haveActiveStage() {
		return $this->stage === 1 OR $this->stage === 2;
	}

	/**
	 * @param  string $user
	 * @return bool
	 */
	public function isOwner($user) {
		return $this->owner === $user;
	}

}
