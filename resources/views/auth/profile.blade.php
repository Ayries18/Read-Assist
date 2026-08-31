@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto p-4 sm:p-6 lg:p-8 animate-fade-in">

        <!-- Alerts -->
        @if ($errors->any())
            <div class="alert alert-error shadow-lg mb-6 bg-red-500/10 border border-red-500/20 text-red-600 rounded-2xl text-sm flex gap-3 p-4" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success shadow-lg mb-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 rounded-2xl text-sm flex gap-3 p-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            <!-- Left Column: Profile Card -->
            <div class="card bg-white border border-black/10 p-6 rounded-2xl flex flex-col items-center text-center shadow-xl">
                <!-- Avatar -->
                <div class="relative w-24 h-24 rounded-full bg-gradient-to-tr from-[#b8860b] to-[#d4a017] flex items-center justify-center text-white text-3xl font-bold shadow-[0_0_20px_rgba(184,134,11,0.25)] mb-4">
                    {{ strtoupper(substr($role === 'admin' ? $account->nama : $account->name, 0, 1)) }}
                    <!-- Glowing ring around the avatar -->
                    <div class="absolute -inset-1 rounded-full border border-[#b8860b]/30 animate-pulse"></div>
                </div>

                <h3 class="text-xl font-bold text-black mb-1 leading-tight">
                    {{ $role === 'admin' ? $account->nama : $account->name }}
                </h3>
                <p class="text-slate-500 text-xs mb-4">
                    {{ $account->email }}
                </p>

                <!-- Role Badge -->
                @if($role === 'admin')
                    <span class="px-3.5 py-1 bg-red-500/10 border border-red-500/20 text-red-600 text-[10px] font-bold rounded-full uppercase tracking-wider">
                        Administrator
                    </span>
                @else
                    <span class="px-3.5 py-1 bg-[#b8860b]/10 border border-[#b8860b]/25 text-[#7a5a00] text-[10px] font-bold rounded-full uppercase tracking-wider">
                        Member
                    </span>
                @endif

                <!-- Profile Metadata Details -->
                <div class="w-full border-t border-black/10 my-6 pt-5 text-left space-y-3">
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500">Metode Login:</span>
                        <span class="text-black font-semibold">Email & Sandi</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500">Status Akun:</span>
                        <span class="text-emerald-600 font-semibold flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block animate-ping"></span>
                            Aktif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Information & Password Forms -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Card 1: Account Information -->
                <div class="card bg-white border border-black/10 p-6 sm:p-8 rounded-2xl shadow-xl">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-[#b8860b]/10 flex items-center justify-center text-[#7a5a00] flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-black">Informasi Profil</h4>
                            <p class="text-xs text-slate-500">Perbarui nama lengkap dan alamat email utama Anda.</p>
                        </div>
                    </div>

                    <form method="POST" action="/profile" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="form-control w-full">
                            <label for="name" class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2 block">Nama Lengkap</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $role === 'admin' ? $account->nama : $account->name) }}" class="input input-bordered w-full bg-white text-black border-black/15 rounded-xl @error('name') border-red-500 @enderror focus:border-[#b8860b] focus:ring-2 focus:ring-[#b8860b]/25 focus:outline-none transition-all duration-300" required @error('name') aria-invalid="true" @enderror>
                            @error('name') <span class="text-red-500 text-xs mt-1 block" role="alert">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control w-full">
                            <label for="email" class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2 block">Alamat Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $account->email) }}" class="input input-bordered w-full bg-white text-black border-black/15 rounded-xl @error('email') border-red-500 @enderror focus:border-[#b8860b] focus:ring-2 focus:ring-[#b8860b]/25 focus:outline-none transition-all duration-300" required @error('email') aria-invalid="true" @enderror>
                            @error('email') <span class="text-red-500 text-xs mt-1 block" role="alert">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="btn btn-primary w-full rounded-xl bg-[#b8860b] hover:bg-[#b8860b] border-none text-white font-semibold transition-all duration-300">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Card 2: Password Management -->
                <div class="card bg-white border border-black/10 p-6 sm:p-8 rounded-2xl shadow-xl">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-[#b8860b]/10 flex items-center justify-center text-[#7a5a00] flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-black">Ganti Password</h4>
                            <p class="text-xs text-slate-500">Amankan akun Anda dengan mengubah sandi secara berkala.</p>
                        </div>
                    </div>

                    <form method="POST" action="/profile/password" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="form-control w-full">
                            <label for="current_password" class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2 block">Password Saat Ini</label>
                            <input type="password" id="current_password" name="current_password" class="input input-bordered w-full bg-white text-black border-black/15 rounded-xl @error('current_password') border-red-500 @enderror focus:border-[#b8860b] focus:ring-2 focus:ring-[#b8860b]/25 focus:outline-none transition-all duration-300" required @error('current_password') aria-invalid="true" @enderror>
                            @error('current_password') <span class="text-red-500 text-xs mt-1 block" role="alert">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-control w-full">
                                <label for="password" class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2 block">Password Baru</label>
                                <input type="password" id="password" name="password" class="input input-bordered w-full bg-white text-black border-black/15 rounded-xl @error('password') border-red-500 @enderror focus:border-[#b8860b] focus:ring-2 focus:ring-[#b8860b]/25 focus:outline-none transition-all duration-300" minlength="6" required @error('password') aria-invalid="true" @enderror>
                                @error('password') <span class="text-red-500 text-xs mt-1 block" role="alert">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control w-full">
                                <label for="password_confirmation" class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2 block">Konfirmasi Password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="input input-bordered w-full bg-white text-black border-black/15 rounded-xl focus:border-[#b8860b] focus:ring-2 focus:ring-[#b8860b]/25 focus:outline-none transition-all duration-300" required>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="btn btn-outline w-full rounded-xl border-black/20 hover:border-[#b8860b] hover:bg-[#b8860b]/10 text-black hover:text-black transition-all duration-300">
                                Ubah Password
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection
