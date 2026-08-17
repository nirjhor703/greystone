<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {
        $user = $request->user();

        $permissions = explode('|', $permission);

        $allowed = $user && collect($permissions)->contains(
            fn (string $item) => $user->hasAdminPermission(trim($item))
        );

        if (! $allowed) {
            $message = 'You do not have permission to access this section.';

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 403);
            }

            if ($request->headers->has('referer')) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'admin_permission' => $message,
                    ]);
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
