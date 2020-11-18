<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('large_image')->nullable();
            $table->string('small_image')->nullable();
            $table->text('description')->nullable();

            $table->decimal('price', 4,2);
            $table->decimal('old_price', 4,2)->nullable();

            $table->boolean('is_on_sale')->nullable();
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->unsignedBigInteger('site_product_id')->nullable();

            $table->boolean('recommended_searched')->nullable();
            $table->boolean('reviews_searched')->nullable();

            $table->string('weight')->nullable();
            $table->string('brand');
            $table->text('storage')->nullable();

            $table->string('dietary_info')->nullable();
            $table->string('allergen_info')->nullable();

            $table->decimal('avg_rating', 4,2)->nullable();
            $table->integer('total_reviews_count')->nullable();
            
            $table->unsignedBigInteger('store_type_id');
            $table->text('url')->nullable();

            $table->timestamp('last_checked')->useCurrent();
            
            $table->foreign('store_type_id')->references('id')->on('store_types');
            $table->foreign('promotion_id')->references('id')->on('promotions');

            $table->unique('site_product_id');

            $table->index('name');
            $table->index('dietary_info');
            $table->index('allergen_info');

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
        Schema::dropIfExists('products');
    }
}
