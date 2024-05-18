<?php

namespace App\Console\Commands;

use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use Illuminate\Console\Command;
use Workerman\Worker;

/** @psalm-suppress PropertyNotSetInConstructor */
class WorkermanCommand extends Command {

	/** @var string */
	protected $name = 'workman';

	/** @var string */
	protected $signature = 'workman {action?} {--d} {--g}';

	/** @var string|null */
	protected $description = 'Start a Workerman server.';

	/**
	 * @return void
	 */
	public function handle() {
		global $argv;
		$action = $this->argument('action');

		$argv[0] = 'wk';
		$argv[1] = $action;
		$argv[2] = '';

		if ($this->option('d')) {
			$argv[2] = '-d';
		}

		if ($this->option('g')) {
			$argv[2] = '-g';
		}

		$this->start();
	}

	/**
	 * @return void
	 */
	private function start() {
		$this->startGateWay();
		$this->startBusinessWorker();
		$this->startRegister();
		Worker::runAll();
	}

	/**
	 * @return void
	 */
	private function startBusinessWorker() {
		$worker                  = new BusinessWorker();
		$worker->name            = 'BusinessWorker';
		$worker->count           = 2;
		$worker->registerAddress = '127.0.0.1:1236';
		$worker->eventHandler    = \App\Events\Workerman::class;
	}

	/**
	 * @return void
	 */
	private function startGateWay() {
		$gateway = new Gateway('websocket://0.0.0.0:'.config('websocket.port'), config('websocket.context'));
		$gateway->transport            = config('websocket.transport');
		$gateway->name                 = 'Gateway';
		$gateway->count                = 1;
		$gateway->lanIp                = '127.0.0.1';
		$gateway->startPort            = 2300;
		$gateway->pingInterval         = 30;
		$gateway->pingNotResponseLimit = 1;
		$gateway->pingData             = '';
		$gateway->registerAddress      = '127.0.0.1:1236';
	}

	/**
	 * @return void
	 */
	private function startRegister() {
		new Register('text://0.0.0.0:1236');
	}

}
