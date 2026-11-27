@extends('layouts.app')

@section('title', 'Masuk Admin - ' . config('shop.name'))

@section('content')
    <div class="max-w-sm mx-auto mt-8">
        <h1 class="text-2xl font-bold mb-5 text-center">Masuk Admin</h1>

        <form method="POST" action="{{ route('admin.login.store') }}" class="bg-white border rounded-xl p-5 space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium mb-1">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1" class="accent-emerald-600"> Ingat saya
            </label>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-lg">Masuk</button>
        </form>
    </div>
@endsection
