<?php
namespace App\Services;

use \Illuminate\Cache\CacheManager;

class WebSocketData {
	/** @var CacheManager */
	private $storage;

	/**
	 * @param CacheManager $storage
	 */
	public function __construct(CacheManager $storage) {
		$this->storage = $storage;
	}

	/**
	 * @param  string $key
	 * @param  mixed  $value
	 * @return bool
	 */
	public function put($key, $value) {
		return $this->storage->put($key, $value);
	}

	/**
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public function get($key, $default = null) {
		return $this->storage->get($key, $default);
	}
}
