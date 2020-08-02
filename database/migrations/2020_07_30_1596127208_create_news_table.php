<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{
	/**
	 * @return void
	 */
	public function up()
	{
		Schema::create('news', function(Blueprint $table) {
			$table->id()->unsigned();
			$table->string('title', 500);
			$table->text('text');
			$table->tinyInteger('active')->unsigned()->default(1);
			$table->timestamps();
		});
	}

	/**
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('news');
	}
}