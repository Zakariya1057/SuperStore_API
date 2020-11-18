<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateScriptHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('script_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('store_type_id')->unique();

            $table->integer('grand_parent_category_index');
            $table->integer('parent_category_index');
            $table->integer('child_category_index');

            $table->integer('product_index');

            $table->string('error_message');
            $table->integer('error_line_number');
            $table->string('error_file');
            
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
        Schema::dropIfExists('script_histories');
    }
}
