<?php

namespace App\Console\Commands;

use App\GrandParentCategory;
use App\Traits\GroceryTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheGroceries extends Command
{
    use GroceryTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:groceries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Caches grocery categories and products.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        
        $this->info('Daily Grocery Cache Start');

        $grand_parent_categories = GrandParentCategory::get();

        foreach($grand_parent_categories as $grand_parent_category){
            $store_type_id = $grand_parent_category->store_type_id;
           
            $this->info('Caching Categories For: '.$grand_parent_category->name);

            $categories = $this->grocery_categories($store_type_id);
            Cache::put('categories_'.$store_type_id, $categories);

            foreach($categories as $category){

                foreach($category->child_categories as $child_category){
                    $this->info('Caching Product Categories For: '.$child_category->name);
                    $product_categories = $this->grocery_products($child_category->id);
                    Cache::put('category_products_'.$child_category->id, $product_categories);
                }

            }

        }

        $this->info('Daily Grocery Cache Complete');

    }

}