<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Rooms;

class RoomsControllerTest extends TestCase
{
	/**
	 * @test
	 * @return void
	 */
	public function CreateMethodTest()
	{
		$faker = \Faker\Factory::create();
		$params = [
			'name' => $faker->name,
			'owner' => md5((string)time()),
		];
		$repository = $this->createMock(\App\Repositories\RoomsRepositoryInterface::class);
		$repository->expects($this->once())->method('create')->with($params)->willReturn(new Rooms);
		$this->app->instance(\App\Repositories\RoomsRepositoryInterface::class, $repository);

		$response = $this->app->call('\App\Http\Controllers\RoomsController@create', ['params' => $params]);

		$this->assertTrue($response instanceof Rooms);
	}

	/**
	 * @test
	 * @return void
	 */
	public function DeleteMethodTest()
	{
		$faker = \Faker\Factory::create();
		$params = [
			'id' => rand(1, 10000),
			'owner' => md5((string)time()),
		];
		$repository = $this->createMock(\App\Repositories\RoomsRepositoryInterface::class);
		$repository->expects($this->once())->method('delete')->with($params)->willReturn(true);
		$this->app->instance(\App\Repositories\RoomsRepositoryInterface::class, $repository);

		$response = $this->app->call('\App\Http\Controllers\RoomsController@delete', ['params' => $params]);

		$this->assertTrue($response);
	}

	/**
	 * @test
	 * @return void
	 */
	public function GetMethodTest()
	{
		$faker = \Faker\Factory::create();
		$params = [
			'owner' => md5((string)time()),
		];
		$repository = $this->createMock(\App\Repositories\RoomsRepositoryInterface::class);
		$repository->expects($this->once())->method('get')->with($params)->willReturn(collect([new Rooms, new Rooms]));
		$this->app->instance(\App\Repositories\RoomsRepositoryInterface::class, $repository);

		$response = $this->app->call('\App\Http\Controllers\RoomsController@get', ['params' => $params]);

		$this->assertTrue(is_array($response), 'Response is array');
		$this->assertTrue(count($response) === 2, 'Response contains two models');
	}

	/**
	 * @test
	 * @return void
	 */
	public function GetWithoutOwnerMethodTest()
	{
		$params = [];
		$repository = $this->createMock(\App\Repositories\RoomsRepositoryInterface::class);
		$repository->expects($this->exactly(0))->method('get')->with($params)->willReturn(collect([new Rooms, new Rooms]));
		$this->app->instance(\App\Repositories\RoomsRepositoryInterface::class, $repository);

		$response = $this->app->call('\App\Http\Controllers\RoomsController@get', ['params' => $params]);

		$this->assertTrue(is_array($response), 'Response is array');
		$this->assertTrue(count($response) === 0, 'Response contains null models');
	}
}
