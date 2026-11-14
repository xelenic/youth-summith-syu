@props(['title' => 'Admin'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ $title }} · {{ config('summit.event.name') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-dvh bg-slate-100 font-sans text-slate-900 antialiased">
        @auth
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
                    <a href="{{ route('admin.registrations.index') }}" class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-summit-red text-sm font-black tracking-tight text-white">SYU</span>
                        <span class="leading-tight">
                            <span class="block font-bold">{{ config('summit.event.name') }}</span>
                            <span class="block text-xs text-slate-500">Registrations admin</span>
                        </span>
                    </a>

                    <div class="flex items-center gap-2 sm:gap-4">
                        <a href="{{ route('registrations.create') }}" target="_blank" rel="noopener" class="hidden text-sm font-medium text-slate-600 hover:text-slate-900 sm:inline">
                            Open form ↗
                        </a>
                        <span class="hidden text-sm text-slate-500 md:inline">{{ auth()->user()->email }}</span>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Sign out</button>
                        </form>
                    </div>
                </div>
            </header>
        @endauth

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8">
            {{ $slot }}
        </main>
    </body>
</html>
