<?php

namespace Tests\Unit;

use App\Listeners\Message;
use App\Services\SocketRouterContract as SocketRouter;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;
use Tests\TestCase;

class MessageTest extends TestCase
{
	/**
	 * @test
	 * @return void
	 */
	public function PingPongTest()
	{
		$client_id = Str::random(16);
		$params = [
			'type' => 'ping',
		];

		$router = $this->createMock(SocketRouter::class);
		$router->expects($this->exactly(0))->method('routing');

		$event = $this->createMock(Dispatcher::class);
		$event->expects($this->exactly(0))->method('dispatch');

		$message = new Message($router, $event);
		$message->handle($client_id, json_encode($params));
	}

	/**
	 * @test
	 * @return void
	 */
	public function RequestTest()
	{
		$client_id = Str::random(16);
		$params = [
			'type' => 'request',
		];

		$router = $this->createMock(SocketRouter::class);
		$router->expects($this->exactly(1))->method('routing')->with($params, $client_id)->willReturn(null);

		$event = $this->createMock(Dispatcher::class);
		$event->expects($this->exactly(0))->method('dispatch');

		$message = new Message($router, $event);
		$message->handle($client_id, json_encode($params));
	}

	/**
	 * @test
	 * @return void
	 */
	public function EventTest()
	{
		$client_id = Str::random(16);
		$params = [
			'action' => 'test.event',
		];

		$router = $this->createMock(SocketRouter::class);
		$router->expects($this->exactly(0))->method('routing');

		$event = $this->createMock(Dispatcher::class);
		$event->expects($this->exactly(1))->method('dispatch')
			->with("socket.{$params['action']}", [$params, $client_id])
			->willReturn(null);

		$message = new Message($router, $event);
		$message->handle($client_id, json_encode($params));
	}

}
