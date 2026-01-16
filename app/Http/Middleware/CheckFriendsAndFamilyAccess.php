<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ffCartItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckFriendsAndFamilyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
            $this->runScheduledTasks();
            
            return $next($request);
    }

    private function runScheduledTasks(): void
    {
        try {
            $lastRun = Cache::get('ff_cart_cleanup_last_run', 0);
            $fiveMinutesAgo = now()->subMinutes(5)->timestamp;

            if ($lastRun < $fiveMinutesAgo) {
                Cache::put('ff_cart_cleanup_last_run', now()->timestamp, 600);
                $cutoff = now()->subMinutes(30);
                ffCartItem::where('updated_at', '<', $cutoff)->delete();
            }
        } catch (\Exception $e) {
            Log::error('Error en el "Poor Man\'s Cron" de FF: ' . $e->getMessage());
        }
    }
}