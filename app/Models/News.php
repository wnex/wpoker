<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class News extends Model {

	/** @var array<string> */
	protected $fillable = ['title', 'text', 'active'];

}
