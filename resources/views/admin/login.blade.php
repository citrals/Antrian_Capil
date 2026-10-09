@extends('layouts.app')

@section('title', 'Login Administrator - Disdukcapil Kabupaten Cirebon')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-slate-100 via-slate-50 to-emerald-50/40">
    <div class="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-3xl border border-slate-200/90 shadow-2xl relative overflow-hidden"
         x-data="{ showPassword: false, isSubmitting: false }">
        
        <!-- Top Decorative Security Accent -->
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-emerald-600 via-teal-600 to-amber-500"></div>

        <!-- Identity & Header -->
        <div class="text-center space-y-3">
            <div class="mx-auto w-16 h-20 bg-slate-50 rounded-2xl p-2 border border-slate-200 shadow-inner flex items-center justify-center">
                <img src="{{ asset('images/kabcirebon.png') }}" alt="Logo Kab Cirebon" class="w-full h-full object-contain">
            </div>

            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                    <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Area Terbatas Petugas</span>
                </span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-2">
                    Autentikasi Administrator
                </h1>
                <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                    Masukkan kata sandi pengelola untuk mengakses dashboard antrean, monitoring kuota, dan reset sistem.
                </p>
            </div>
        </div>

        <!-- Error Message Alert -->
        @if($errors->has('password'))
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 flex items-start gap-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <span class="font-bold block">Gagal Masuk:</span>
                    <span>{{ $errors->first('password') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-800 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Form Login -->
        <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-5" @submit="isSubmitting = true">
            @csrf

            <div>
                <label for="admin_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Kata Sandi Admin
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>

                    <input :type="showPassword ? 'text' : 'password'" 
                           id="admin_password" 
                           name="password" 
                           required 
                           autofocus
                           placeholder="Masukkan sandi..." 
                           class="w-full pl-11 pr-11 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium text-slate-900 transition-all">

                    <button type="button" 
                            @click="showPassword = !showPassword" 
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-hidden"
                            tabindex="-1">
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                    </button>
                </div>

            </div>

            <button type="submit" 
                    :disabled="isSubmitting"
                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-sm shadow-lg shadow-emerald-700/25 hover:shadow-emerald-700/35 transition-all transform hover:-translate-y-0.5 disabled:opacity-50">
                <span x-show="!isSubmitting" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Masuk ke Panel Pengelola</span>
                </span>
                <span x-show="isSubmitting" x-cloak class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Memverifikasi Sandi...</span>
                </span>
            </button>
        </form>

        <!-- Back to Citizen Home link -->
        <div class="pt-4 border-t border-slate-200/80 text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Halaman Pendaftaran Antrean Warga</span>
            </a>
        </div>
    </div>
</div>
@endsection
