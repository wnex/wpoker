<?php

namespace Tests\Unit;

use App\Services\SocketRouter;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;
use Tests\TestCase;

class SocketRouterTest extends TestCase
{
	/**
	 * @test
	 * @return void
	 */
	public function AddAndGetRoutesTest()
	{
		$params = [
			'route' => 'test.route',
			'class' => SocketRouterTest::class . '@test',
		];

		$container = $this->createMock(Container::class);
		$container->expects($this->exactly(0))->method('call');

		$event = $this->createMock(Dispatcher::class);
		$event->expects($this->exactly(0))->method('dispatch');

		$router = new SocketRouter($container, $event);
		$router->add($params['route'], $params['class']);

		$routes = $router->all();

		$this->assertTrue(count($routes) === 1);
		$this->assertTrue($routes[$params['route']] === $params['class']);
	}

	/**
	 * @test
	 * @return void
	 */
	public function RoutingTest()
	{
		$client_id = Str::random(16);
		$routesParams = [
			'route' => 'test.route',
			'class' => SocketRouterTest::class . '@test',
		];
		$params = [
			'type' => 'request',
			'action' => $routesParams['route'],
			'params' => ['parametr' => 'test'],
			'request_id' => rand(1, 10000),
		];

		$response = ['test' => true];

		$container = $this->createMock(Container::class);
		$container->expects($this->exactly(1))->method('call')
			->with($routesParams['class'], [
				'params' => $params['params'],
				'client_id' => $client_id,
			])->willReturn($response);

		$event = $this->createMock(Dispatcher::class);
		$event->expects($this->exactly(1))->method('dispatch')
			->with('server.socket.sendToAll', [[
				'type' => 'request',
				'action' => $params['action'],
				'request_id' => $params['request_id'],
				'data' => $response,
			], [$client_id]])->willReturn(null);

		$router = new SocketRouter($container, $event);
		$router->add($routesParams['route'], $routesParams['class']);

		$router->routing($params, $client_id);
	}

}
