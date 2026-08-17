<?php

namespace App\Http\Middleware;

use App\Services\AdminNotificationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureAdminIsActive
{
    public function __construct(
        private readonly AdminNotificationService $notifications
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'This admin account is inactive.');
        }

        if ($user && $request->is('admin/*')) {
            $this->runDailyLowStockFallback();
        }

        return $next($request);
    }

    private function runDailyLowStockFallback(): void
    {
        try {
            $timezone = config('app.timezone', 'Asia/Dhaka');
            $now = now($timezone);

            if ($now->hour < 8) {
                return;
            }

            $cacheKey = 'admin-low-stock-fallback:'.$now->toDateString();

            if (! Cache::add($cacheKey, true, $now->copy()->endOfDay())) {
                return;
            }

            $this->notifications->checkLowStockProducts();
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
