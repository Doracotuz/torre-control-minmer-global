<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\ffCartItem;

class FFClearExpiredCarts extends Command
{
    protected $signature = 'ff:clear-carts';
    protected $description = 'Libera items de carritos abandonados (ej. > 30 min)';

    public function handle()
    {
        $cutoff = now()->subMinutes(30);
        $count = ffCartItem::where('updated_at', '<', $cutoff)->delete();
        $this->info("Se liberaron $count items de carritos abandonados.");
    }
}