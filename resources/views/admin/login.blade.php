<x-layouts.admin title="Sign in">
    <div class="mx-auto mt-4 max-w-sm overflow-hidden rounded-3xl bg-white shadow-xl shadow-slate-900/10 ring-1 ring-slate-200 sm:mt-10">
        <img src="{{ asset('images/summit-header.jpg') }}" width="1058" height="728" alt="SYU Youth Summit" class="block h-auto w-full">

        <div class="p-6">
            <h1 class="text-xl font-bold tracking-tight">Admin sign in</h1>
            <p class="mt-1 text-sm text-slate-500">View and export summit registrations.</p>

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 flex flex-col gap-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        required
                        autofocus
                        class="mt-1.5 block h-11 w-full rounded-lg border border-slate-300 px-3 text-slate-900 focus:border-summit-red focus:ring-4 focus:ring-summit-red/15 focus:outline-none"
                        @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                    >
                    @error('email')
                        <p id="email-error" class="mt-1.5 text-sm font-medium text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="mt-1.5 block h-11 w-full rounded-lg border border-slate-300 px-3 text-slate-900 focus:border-summit-red focus:ring-4 focus:ring-summit-red/15 focus:outline-none"
                    >
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="size-4 rounded accent-summit-red">
                    Keep me signed in
                </label>

                <button type="submit" data-busy-label="Signing in…" class="mt-2 h-11 rounded-lg bg-summit-red font-semibold text-white shadow-sm transition hover:bg-summit-red-dark disabled:opacity-80">
                    Sign in
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
