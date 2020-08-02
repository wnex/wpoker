<?php
namespace App\Services;

interface SocketRouterContract
{

	/**
	 * @param  array  $data
	 * @param  string $client_id
	 * @return bool
	 */
	public function routing($data, $client_id);

	/**
	 * @param  string $route
	 * @param  string $class
	 * @return void
	 */
	public function add($route, $class);

}
