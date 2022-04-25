<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Rooms;
use App\Models\Tasks;

class TasksControllerTest extends TestCase
{
	/**
	 * @test
	 * @return void
	 */
	public function CreateMethodTest()
	{
		$faker = \Faker\Factory::create();
		$params = [
			'user' => md5((string)time()),
			'room' => $faker->bothify('**********'),
			'text' => $faker->text,
		];
		$tasksRepository = $this->createMock(\App\Repositories\TasksRepositoryInterface::class);
		$tasksRepository->expects($this->once())->method('create')->with([
			'text' => $params['text'],
			'owner' => $params['user'],
			'room' => $params['room'],
		])->willReturn(new Tasks);
		$this->app->instance(\App\Repositories\TasksRepositoryInterface::class, $tasksRepository);

		$response = $this->app->call('\App\Http\Controllers\TasksController@create', ['params' => $params]);

		$this->assertTrue($response instanceof Tasks);
	}

	/**
	 * @test
	 * @return void
	 */
	public function UpdateMethodTest()
	{
		$faker = \Faker\Factory::create();
		$params = [
			'id' => rand(1, 10000),
			'user' => md5((string)time()),
			'text' => $faker->text,
		];
		$tasksRepository = $this->createMock(\App\Repositories\TasksRepositoryInterface::class);
		$tasksRepository->expects($this->once())->method('update')->with($params)->willReturn(new Tasks);
		$this->app->instance(\App\Repositories\TasksRepositoryInterface::class, $tasksRepository);

		$response = $this->app->call('\App\Http\Controllers\TasksController@update', ['params' => $params]);

		$this->assertTrue($response instanceof Tasks);
	}

	/**
	 * @test
	 * @return void
	 */
	public function DeleteMethodTest()
	{
		$params = [
			'id' => rand(1, 10000),
			'user' => md5((string)time()),
		];
		$tasksRepository = $this->createMock(\App\Repositories\TasksRepositoryInterface::class);
		$tasksRepository->expects($this->once())->method('delete')->with([
			'id' => $params['id'],
			'owner' => $params['user'],
		])->willReturn(true);
		$this->app->instance(\App\Repositories\TasksRepositoryInterface::class, $tasksRepository);

		$response = $this->app->call('\App\Http\Controllers\TasksController@delete', ['params' => $params]);

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
			'room' => $faker->bothify('**********'),
		];
		$tasksRepository = $this->createMock(\App\Repositories\TasksRepositoryInterface::class);
		$tasksRepository->expects($this->once())->method('getAllFromRoom')
			->with($params['room'])
			->willReturn(new \Illuminate\Database\Eloquent\Collection([new Tasks, new Tasks]));
		$this->app->instance(\App\Repositories\TasksRepositoryInterface::class, $tasksRepository);

		$response = $this->app->call('\App\Http\Controllers\TasksController@get', ['params' => $params]);

		$this->assertTrue(is_array($response), 'Response is array');
		$this->assertTrue(count($response) === 2, 'Response contains two models');
	}
}
