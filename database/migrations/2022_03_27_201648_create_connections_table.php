<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConnectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('connections', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->string('id', 20);
            $table->string('uid', 36)->default('');
            $table->string('room_id', 36)->default('');
            $table->string('name', 150)->default('');
            $table->boolean('active')->default(true);
            $table->json('vote');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('connections');
    }
}
