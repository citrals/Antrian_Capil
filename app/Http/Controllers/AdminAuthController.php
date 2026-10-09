<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    /**
     * Show the admin password login form.
     */
    public function showLoginForm(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('admin_authenticated', false)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Authenticate admin access via password.
     */
    public function login(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Kata sandi pengelola wajib diisi.',
        ]);

        $expectedPassword = env('ADMIN_PASSWORD', 'admin123');

        if ($validated['password'] !== $expectedPassword) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kata sandi pengelola tidak valid. Silakan coba lagi.',
                ], 422);
            }

            return back()
                ->withErrors(['password' => 'Kata sandi pengelola tidak valid. Silakan masukkan sandi yang benar.'])
                ->withInput();
        }

        // Mark admin session as authenticated
        $request->session()->put('admin_authenticated', true);
        $request->session()->put('admin_logged_in_at', now()->toIso8601String());
        $request->session()->regenerate();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Autentikasi admin berhasil.',
                'redirect_url' => route('admin.dashboard'),
            ]);
        }

        return redirect()->intended(route('admin.dashboard'))
            ->with('success', 'Selamat datang di Panel Pengelola Antrean Disdukcapil Kabupaten Cirebon.');
    }

    /**
     * Log out of the admin panel.
     */
    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['admin_authenticated', 'admin_logged_in_at']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Anda telah berhasil keluar dari Panel Administrator.');
    }
}
