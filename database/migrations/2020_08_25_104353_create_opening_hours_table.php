<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateOpeningHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('opening_hours', function (Blueprint $table) {
            $table->id();

            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();

            $table->smallInteger('day_of_week');
            $table->unsignedBigInteger('store_id');

            $table->boolean('closed_today')->default(0);

            $table->foreign('store_id')->references('id')->on('stores');
            
            $table->index('store_id');
            $table->index('day_of_week');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opening_hours');
    }
}
