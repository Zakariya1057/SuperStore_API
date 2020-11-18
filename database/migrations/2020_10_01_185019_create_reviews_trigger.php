<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE TRIGGER trigger_update_product_reviews AFTER UPDATE ON `reviews` FOR EACH ROW
        BEGIN
            UPDATE products SET avg_rating = (SELECT avg(rating) FROM reviews WHERE product_id = OLD.product_id), total_reviews_COUNT = (SELECT COUNT(*) FROM reviews WHERE product_id = NEW.product_id) WHERE products.id = NEW.product_id;
        END
        ');

        DB::unprepared('
        CREATE TRIGGER trigger_insert_product_reviews AFTER INSERT ON `reviews` FOR EACH ROW
        BEGIN
            UPDATE products SET avg_rating = (SELECT avg(rating) FROM reviews WHERE product_id = NEW.product_id), total_reviews_COUNT = (SELECT COUNT(*) FROM reviews WHERE product_id = NEW.product_id) WHERE products.id = NEW.product_id;
        END
        ');

        DB::unprepared('
        CREATE TRIGGER trigger_delete_product_reviews AFTER DELETE ON `reviews` FOR EACH ROW
        BEGIN
            UPDATE products SET avg_rating = (SELECT avg(rating) FROM reviews WHERE product_id = OLD.product_id), total_reviews_COUNT = (SELECT COUNT(*) FROM reviews WHERE product_id = OLD.product_id) WHERE products.id = OLD.product_id;
        END
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER `trigger_update_product_reviews`');
        DB::unprepared('DROP TRIGGER `trigger_delete_product_reviews`');
        DB::unprepared('DROP TRIGGER `trigger_insert_product_reviews`');
    }
}
