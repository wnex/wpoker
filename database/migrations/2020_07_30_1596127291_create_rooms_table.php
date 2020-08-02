<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsTable extends Migration
{
	/**
	 * @return void
	 */
	public function up()
	{
		Schema::create('rooms', function(Blueprint $table) {
			$table->id()->unsigned();
			$table->string('name', 50);
			$table->string('hash', 10)->unique();
			$table->tinyInteger('stage')->default(0);
			$table->string('owner', 64);
			$table->timestamps();
		});
	}

	/**
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('rooms');
	}
}