<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RunCommandOnceDaily
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cacheKey = 'daily_command_ran';
        if (! Cache::has($cacheKey)) {
            Artisan::call('customers:installment-remind');
            Artisan::call('products:low-stock-alert');
            Artisan::call('filters:candle-remind');
            Artisan::call('suppliers:installments-remind');

            // Set cache to prevent running again for 24 hours
            Cache::put($cacheKey, true, now()->addDay());
        }

        return $next($request);
    }
}
