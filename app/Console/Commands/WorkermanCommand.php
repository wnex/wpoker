<?php

namespace App\Console\Commands;

use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use Illuminate\Console\Command;
use Workerman\Worker;

class WorkermanCommand extends Command
{

	protected $signature = 'workman {action?} {--d} {--g}';

	protected $description = 'Start a Workerman server.';

	public function handle()
	{
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

	private function start()
	{
		$this->startGateWay();
		$this->startBusinessWorker();
		$this->startRegister();
		Worker::runAll();
	}

	private function startBusinessWorker()
	{
		$worker                  = new BusinessWorker();
		$worker->name            = 'BusinessWorker';
		$worker->count           = 2;
		$worker->registerAddress = '127.0.0.1:1236';
		$worker->eventHandler    = \App\Events\Workerman::class;
	}

	private function startGateWay()
	{
		$gateway = new Gateway("websocket://0.0.0.0:3000", config('websocket.context'));
		$gateway->transport            = config('websocket.transport');
		$gateway->name                 = 'Gateway';
		$gateway->count                = 1;
		$gateway->lanIp                = '127.0.0.1';
		$gateway->startPort            = 2300;
		//$gateway->pingInterval         = 30;
		//$gateway->pingNotResponseLimit = 0;
		//$gateway->pingData             = '{"type":"@heart@"}';
		$gateway->registerAddress      = '127.0.0.1:1236';
	}

	private function startRegister()
	{
		new Register('text://0.0.0.0:1236');
	}
}
