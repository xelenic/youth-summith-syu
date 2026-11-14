@php
    $event = config('summit.event');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#d3111c">

        <title>Register · {{ $event['name'] }}</title>
        <meta name="description" content="Register for the {{ $event['name'] }} — {{ $event['date'] }} at {{ $event['time'] }}, {{ $event['venue'] }}.">

        <meta property="og:type" content="website">
        <meta property="og:title" content="Register for the {{ $event['name'] }}">
        <meta property="og:description" content="{{ $event['date'] }} at {{ $event['time'] }} · {{ $event['venue'] }}">
        <meta property="og:image" content="{{ asset('images/summit-header.jpg') }}">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-dvh bg-summit-paper font-sans text-slate-900 antialiased sm:bg-slate-200">
        {{-- Blurred poster behind the card on larger screens, like the design mockup. --}}
        <div aria-hidden="true" class="pointer-events-none fixed inset-0 hidden overflow-hidden sm:block">
            <img src="{{ asset('images/summit-header.jpg') }}" alt="" class="size-full scale-110 object-cover opacity-70 blur-2xl">
            <div class="absolute inset-0 bg-white/45"></div>
        </div>

        <main class="relative mx-auto flex min-h-dvh max-w-md flex-col overflow-hidden bg-summit-paper sm:my-10 sm:min-h-0 sm:rounded-[2rem] sm:shadow-2xl sm:shadow-slate-900/25 sm:ring-1 sm:ring-black/5">
            <img
                src="{{ asset('images/summit-header.jpg') }}"
                width="1058"
                height="728"
                fetchpriority="high"
                class="block h-auto w-full"
                alt="SYU Youth Summit — Nov 15 at 2.00 PM, Buttala. රටක් හදන තරුණ ජවය! நாட்டைக் கட்டியெழுப்பும் இளைஞர் சக்தி! #SYU Sri Lanka"
            >

            <section class="relative px-6 pt-4 pb-10">
                <div aria-hidden="true" class="summit-halftone pointer-events-none absolute -right-10 top-20 h-44 w-32"></div>
                <div aria-hidden="true" class="summit-halftone pointer-events-none absolute -left-12 top-[22rem] h-56 w-32"></div>

                @if ($registration)
                    <div class="relative rounded-3xl bg-white p-6 text-center shadow-xl shadow-slate-900/5 ring-1 ring-slate-200">
                        <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-green-100 text-green-600">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="size-8" aria-hidden="true">
                                <path d="M5 12.5l4.5 4.5L19 7.5" />
                            </svg>
                        </div>

                        <h1 class="mt-4 text-2xl font-extrabold uppercase">You're registered!</h1>
                        <p class="mt-2 text-slate-600">
                            Thank you, <span class="font-semibold text-slate-900">{{ $registration->full_name }}</span>.
                            See you on {{ $event['date'] }} at {{ $event['time'] }}, {{ $event['venue'] }}.
                        </p>

                        <div class="mt-5 rounded-2xl border-2 border-dashed border-summit-red/40 bg-red-50 px-4 py-3">
                            <p class="text-xs font-semibold tracking-widest text-summit-red uppercase">Registration no.</p>
                            <p class="mt-1 text-3xl font-black tracking-wider text-slate-900">{{ $registration->reference }}</p>
                        </div>

                        <dl class="mt-5 divide-y divide-slate-100 text-left text-sm">
                            <div class="flex justify-between gap-4 py-2.5">
                                <dt class="text-slate-500">WhatsApp</dt>
                                <dd class="font-medium text-slate-900">{{ $registration->local_whatsapp_number }}</dd>
                            </div>
                            <div class="flex justify-between gap-4 py-2.5">
                                <dt class="text-slate-500">GN Division</dt>
                                <dd class="text-right font-medium text-slate-900">{{ $registration->gn_division }}</dd>
                            </div>
                        </dl>

                        <p class="mt-4 text-sm text-slate-500">Take a screenshot of this screen and show it at the entrance.</p>

                        <a href="{{ route('registrations.create') }}" class="mt-6 flex h-12 items-center justify-center rounded-full border-2 border-summit-red font-bold text-summit-red transition hover:bg-summit-red hover:text-white">
                            Register another person
                        </a>
                    </div>
                @else
                    <h1 class="relative text-center text-[1.7rem] font-extrabold uppercase">Registration Form</h1>

                    <form method="POST" action="{{ route('registrations.store') }}" class="relative mt-6 flex flex-col gap-6">
                        @csrf

                        <div>
                            <label for="full_name" class="block text-[1.05rem] font-semibold">Full Name</label>
                            <input
                                id="full_name"
                                name="full_name"
                                type="text"
                                value="{{ old('full_name') }}"
                                autocomplete="name"
                                maxlength="120"
                                required
                                placeholder="Enter your full name"
                                class="summit-input mt-2"
                                @error('full_name') aria-invalid="true" aria-describedby="full_name-error" @enderror
                            >
                            @error('full_name')
                                <p id="full_name-error" class="mt-1.5 text-sm font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="whatsapp_number" class="block text-[1.05rem] font-semibold">WhatsApp Number</label>
                            <div class="relative mt-2">
                                <x-icons.whatsapp class="pointer-events-none absolute top-1/2 left-4 size-7 -translate-y-1/2 text-whatsapp" />
                                <input
                                    id="whatsapp_number"
                                    name="whatsapp_number"
                                    type="tel"
                                    inputmode="tel"
                                    value="{{ old('whatsapp_number') }}"
                                    autocomplete="tel"
                                    maxlength="20"
                                    required
                                    placeholder="e.g., +94 7X XXX XXXX"
                                    class="summit-input pl-14"
                                    @error('whatsapp_number') aria-invalid="true" aria-describedby="whatsapp_number-error" @enderror
                                >
                            </div>
                            @error('whatsapp_number')
                                <p id="whatsapp_number-error" class="mt-1.5 text-sm font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="gn_division" class="block text-[1.05rem] font-semibold">GN Division (ග්&zwj;රාම නිලධාරී වසම)</label>
                            <div class="relative mt-2">
                                <input
                                    id="gn_division"
                                    name="gn_division"
                                    type="text"
                                    list="gn-division-options"
                                    value="{{ old('gn_division') }}"
                                    autocomplete="off"
                                    maxlength="100"
                                    required
                                    placeholder="Search or enter your GN Division"
                                    class="summit-input pr-11"
                                    @error('gn_division') aria-invalid="true" aria-describedby="gn_division-error" @enderror
                                >
                                <button type="button" tabindex="-1" aria-hidden="true" data-open-picker="gn_division" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-500">
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
                                        <path d="M5 7.5l5 5 5-5" />
                                    </svg>
                                </button>
                                <datalist id="gn-division-options">
                                    @foreach ($gnDivisions as $division)
                                        <option value="{{ $division }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            @error('gn_division')
                                <p id="gn_division-error" class="mt-1.5 text-sm font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="flex cursor-pointer items-center gap-3 text-[1.05rem] text-slate-800">
                                <span class="relative flex size-6 shrink-0">
                                    <input
                                        type="checkbox"
                                        name="terms"
                                        value="1"
                                        required
                                        @checked(old('terms'))
                                        class="peer size-6 cursor-pointer appearance-none rounded-md border-2 border-slate-300 bg-white transition checked:border-summit-red checked:bg-summit-red focus-visible:ring-4 focus-visible:ring-summit-red/20 focus-visible:outline-none"
                                        @error('terms') aria-invalid="true" aria-describedby="terms-error" @enderror
                                    >
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="pointer-events-none absolute inset-0 m-auto size-4 text-white opacity-0 peer-checked:opacity-100" aria-hidden="true">
                                        <path d="M4.5 10.5l3.5 3.5 7.5-8" />
                                    </svg>
                                </span>
                                I agree to the terms and conditions
                            </label>
                            @error('terms')
                                <p id="terms-error" class="mt-1.5 text-sm font-medium text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            data-busy-label="Registering…"
                            class="mt-2 h-16 w-full rounded-full bg-linear-to-b from-[#e8141f] to-summit-red-dark text-[1.6rem] font-extrabold tracking-wide text-white uppercase shadow-lg shadow-red-900/30 transition hover:brightness-110 focus-visible:ring-4 focus-visible:ring-summit-red/30 focus-visible:outline-none active:scale-[0.99] disabled:cursor-wait disabled:opacity-80"
                        >
                            Register Now
                        </button>
                    </form>
                @endif
            </section>

            <footer class="mt-auto">
                <img src="{{ asset('images/summit-crowd.png') }}" width="1058" height="175" alt="" class="block h-auto w-full">
                <p class="bg-summit-maroon px-6 pt-1 pb-4 text-center text-xs font-semibold tracking-[0.25em] text-white/70 uppercase">#SYU Sri Lanka</p>
            </footer>
        </main>
    </body>
</html>
