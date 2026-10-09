<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthMiddleware
{
    /**
     * Handle an incoming request.
     * Ensure only authenticated admin sessions can access admin routes.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('admin_authenticated', false)) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'unauthorized',
                    'message' => 'Sesi autentikasi admin tidak valid atau telah berakhir. Silakan login kembali.',
                ], 401);
            }

            return redirect()->route('admin.login')
                ->with('error', 'Silakan masukkan kata sandi pengelola untuk mengakses panel Admin.');
        }

        return $next($request);
    }
}
