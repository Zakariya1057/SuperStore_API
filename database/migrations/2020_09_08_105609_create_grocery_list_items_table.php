<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateGroceryListItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('grocery_list_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->unsignedBigInteger('list_id');
            $table->unsignedBigInteger('parent_category_id');
            $table->decimal('total_price', 6,2)->default(0);

            $table->boolean('ticked_off')->default(0);

            $table->foreign('list_id')->references('id')->on('grocery_lists');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('parent_category_id')->references('id')->on('parent_categories');

            $table->index('list_id');
            $table->unique(['product_id', 'list_id','parent_category_id']);

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
        Schema::dropIfExists('lists_items');
    }
}
