<?php

namespace App\Console\Commands;

use App\Traits\GroceryListTrait;
use App\Traits\GroceryTrait;
use App\Traits\PromotionTrait;
use App\Traits\StoreTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheHome extends Command
{
    use StoreTrait;
    use PromotionTrait;
    use GroceryListTrait;
    use GroceryTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:home';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Caches home for decreasing loading speed.';

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
    public function handle()
    {
        $this->info('Weekly Home Cache Start');

        $featured_items = $this->featured_items();
        $stores = $this->stores_by_type(1,false);
        $categories = $this->home_categories();
        $promotions = $this->store_promotions(1);

        $data = [
            'stores' => $stores,
            'featured' => $featured_items,
            'promotions' => $promotions,
            'categories' => $categories,
        ];

        Cache::put('home_page', $data);

        $this->info('Weekly Home Cache Complete');
        
    }
}