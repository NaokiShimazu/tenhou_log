<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenhousTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenhous', function (Blueprint $table) {
            $table->increments('id');
            $table->string('real_name')->default(' ');
            $table->string('tenhou_name')->default(' ');
            $table->string('twitter_id')->default(' ');
            $table->string('month');
            $table->string('latest_grade');
            $table->integer('latest_point');
            $table->string('last_month_grade');
            $table->integer('last_month_point');
            $table->boolean('upgrade')->default(false);
            $table->boolean('downgrade')->default(false);
            $table->integer('frequency');
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
        Schema::dropIfExists('tenhous');
    }
}
