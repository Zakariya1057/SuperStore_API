<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCategoryProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_products', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('child_category_id');
            $table->unsignedBigInteger('parent_category_id');
            $table->unsignedBigInteger('grand_parent_category_id');

            $table->unsignedBigInteger('store_id')->nullable();
            
            $table->foreign('child_category_id')->references('id')->on('child_categories');
            $table->foreign('parent_category_id')->references('id')->on('parent_categories');
            $table->foreign('grand_parent_category_id')->references('id')->on('grand_parent_categories');
            $table->foreign('store_id')->references('id')->on('stores');
            $table->foreign('product_id')->references('id')->on('products');


            $table->index('product_id');
            $table->index('child_category_id');
            $table->index('parent_category_id');
            $table->index('grand_parent_category_id');

            $table->unique(['product_id', 'child_category_id', 'parent_category_id', 'grand_parent_category_id'], 'unique_product_categories');

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
        Schema::dropIfExists('category_products');
    }
}
