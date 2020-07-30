<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
	public function up()
	{
		Schema::create('tasks', function(Blueprint $table) {
			$table->id()->unsigned();
			$table->text('text');
			$table->smallInteger('order');
			$table->bigInteger('room_id')->unsigned();
			$table->string('story_point', 50)->nullable();
			$table->string('story_point_view', 50)->nullable();
			$table->timestamps();
			$table->foreign('room_id')->references('id')->on('rooms');
		});

		Schema::table('rooms', function(Blueprint $table) {
			$table->bigInteger('active_task_id')->unsigned()->nullable()->after('owner');
			$table->foreign('active_task_id')->references('id')->on('tasks');
		});
	}

	public function down()
	{
		Schema::table('tasks', function(Blueprint $table) {
			$table->dropForeign(['room_id']);
		});
		Schema::table('rooms', function(Blueprint $table) {
			$table->dropForeign(['active_task_id']);
		});
		Schema::dropIfExists('tasks');
	}
}