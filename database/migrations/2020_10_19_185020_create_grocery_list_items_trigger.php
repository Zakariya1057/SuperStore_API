<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateGroceryListItemsTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE TRIGGER trigger_update_grocery_list_items AFTER UPDATE ON `grocery_list_items` FOR EACH ROW
        BEGIN
            UPDATE grocery_lists SET ticked_off_items = (SELECT COUNT(*) FROM grocery_list_items WHERE grocery_list_items.list_id = NEW.list_id AND ticked_off = 1), total_items = (SELECT COUNT(*) FROM grocery_list_items WHERE grocery_list_items.list_id = NEW.list_id) WHERE grocery_lists.id = NEW.list_id;
        END
        ');

        DB::unprepared('
        CREATE TRIGGER trigger_insert_grocery_list_items AFTER INSERT ON `grocery_list_items` FOR EACH ROW
        BEGIN
        UPDATE grocery_lists SET ticked_off_items = (SELECT COUNT(*) FROM grocery_list_items WHERE grocery_list_items.list_id = NEW.list_id AND ticked_off = 1), total_items = (SELECT COUNT(*) FROM grocery_list_items WHERE grocery_list_items.list_id = NEW.list_id) WHERE grocery_lists.id = NEW.list_id;
        END
        ');

        DB::unprepared('
        CREATE TRIGGER trigger_delete_grocery_list_items AFTER DELETE ON `grocery_list_items` FOR EACH ROW
        BEGIN
        UPDATE grocery_lists SET ticked_off_items = (SELECT COUNT(*) FROM grocery_list_items WHERE grocery_list_items.list_id = OLD.list_id AND ticked_off = 1), total_items = (SELECT COUNT(*) FROM grocery_list_items WHERE grocery_list_items.list_id = OLD.list_id) WHERE grocery_lists.id = OLD.list_id;
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
        DB::unprepared('DROP TRIGGER `trigger_update_grocery_list_items`');
        DB::unprepared('DROP TRIGGER `trigger_insert_grocery_list_items`');
        DB::unprepared('DROP TRIGGER `trigger_delete_grocery_list_items`');
    }
}
