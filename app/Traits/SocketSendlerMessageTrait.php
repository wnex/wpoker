<?php
namespace App\Traits;

use Workerman\Timer;
use GatewayWorker\Lib\Gateway;

trait SocketSendlerMessageTrait {

	/**
	 * @param  array                        $data
	 * @param  array<array-key, mixed>|null $client_id_array
	 * @param  array<array-key, mixed>|null $exclude_client_id
	 * @return null
	 */
	public function sendToAll($data, $client_id_array = null, $exclude_client_id = null)
	{
		return Gateway::sendToAll(json_encode($data), $client_id_array, $exclude_client_id);
	}

	/**
	 * @param  array  $data
	 * @return bool
	 */
	public function sendToCurrentClient($data)
	{
		return Gateway::sendToCurrentClient(json_encode($data));
	}

	/**
	 * Add a timer.
	 *
	 * @param float    $time_interval
	 * @param callable $func
	 * @param mixed    $args
	 * @param bool     $persistent
	 * @return int|bool
	 */
	public function addTimer($time_interval, $func, $args = [], $persistent = true)
	{
		return Timer::add($time_interval, $func, $args, $persistent);
	}

	/**
	 * 将 client_id 与 uid 绑定
	 *
	 * @param string     $client_id
	 * @param int|string $uid
	 * @return void
	 */
	public function bindUid($client_id, $uid)
	{
		Gateway::bindUid($client_id, $uid);
	}

	/**
	 * 将 client_id 与 uid 解除绑定
	 *
	 * @param string     $client_id
	 * @param int|string $uid
	 * @return void
	 */
	public function unbindUid($client_id, $uid)
	{
		Gateway::unbindUid($client_id, $uid);
	}

	/**
	 * 将 client_id 加入组
	 *
	 * @param string $client_id
	 * @param int|string $group
	 * @return void
	 */
	public function joinGroup($client_id, $group)
	{
		Gateway::joinGroup($client_id, $group);
	}

	/**
	 * 将 client_id 离开组
	 *
	 * @param string $client_id
	 * @param int|string $group
	 *
	 * @return void
	 */
	public function leaveGroup($client_id, $group)
	{
		Gateway::leaveGroup($client_id, $group);
	}


	/**
	 * 获取与 uid 绑定的 client_id 列表
	 *
	 * @param string $uid
	 * @return array
	 */
	public function getClientIdByUid($uid)
	{
		return Gateway::getClientIdByUid($uid);
	}

}
