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
 * @property string $cardset
 * @property int|null $active_task_id
 * @property string $password
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

	/** @var array */
	protected $fillable = ['name', 'owner', 'active_task_id', 'cardset'];

	/** @var array */
	protected $casts = [
		'cardset' => 'array',
	];

	protected $appends = ['hasPassword'];
	protected $hidden = ['password'];

	/**
	 * @return void
	 */
	protected static function boot() {
		parent::boot();

		static::creating(function(Model $query) {
			$query->hash = trim(file_get_contents('/proc/sys/kernel/random/uuid'));
		});
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function tasks() {
		return $this->hasMany(Tasks::class, 'room_id');
	}

	/**
	 * @return bool
	 */
	public function getHasPasswordAttribute() {
		return !empty($this->password);
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function activeTask() {
		return $this->hasOne(Tasks::class, 'id', 'active_task_id');
	}

	/**
	 * @return Tasks
	 */
	public function getNextTask() {
		return $this->tasks()->whereNull('story_point')->orderBy('order', 'asc')->firstOrNew([]);
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

	/**
	 * @param  bool $withVote
	 * @return array
	 */
	public function getRoomUsers($withVote = false)
	{
		$connects = Connections::where('room_id', $this->hash)->distinct('uid')->get();

		$users = [];
		foreach ($connects as $connect) {
			$user = [
				'id' => $connect->id,
				'name' => $connect->name,
				'active' => $connect->active,
				'isVoted' => isset($connect->vote['is_voted']) ? $connect->vote['is_voted'] : false,
				'isOwner' => $this->isOwner($connect['uid']),
				'hasVote' => $connect->vote['has_vote'],
			];

			if ($withVote AND isset($connect->vote['value'])) {
				$user['vote'] = $connect->vote['value'];
				$user['voteView'] = $connect->vote['view'];
			}

			$users[] = $user;
		}

		return $users;
	}

	/**
	 * Get the route key for the model.
	 *
	 * @return string
	 */
	public function getRouteKeyName()
	{
		return 'hash';
	}

}
