@php
    $event = config('summit.event');
    $isFiltered = $filters['search'] !== '' || $filters['gn_division'] !== '';
    $largestDivisionCount = max(1, (int) $divisionCounts->max('total'));
    $topDivisions = $divisionCounts->take(10);
@endphp

<x-layouts.admin title="Registrations">
    @if (session('status'))
        <div role="status" class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Registrations</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $event['date'] }} at {{ $event['time'] }} · {{ $event['venue'] }}</p>
        </div>

        <a
            href="{{ route('admin.registrations.export', array_filter($filters)) }}"
            class="inline-flex items-center gap-2 rounded-lg bg-summit-red px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-summit-red-dark"
        >
            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true">
                <path d="M10 3v10m0 0l-4-4m4 4l4-4M4 16h12" />
            </svg>
            {{ $isFiltered ? 'Download filtered CSV' : 'Download CSV' }}
        </a>
    </div>

    <dl class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <dt class="text-sm text-slate-500">Total registrations</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight tabular-nums">{{ number_format($totalCount) }}</dd>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <dt class="text-sm text-slate-500">Registered today</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight tabular-nums">{{ number_format($todayCount) }}</dd>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <dt class="text-sm text-slate-500">GN divisions</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight tabular-nums">{{ number_format($divisionCounts->count()) }}</dd>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <dt class="text-sm text-slate-500">Latest registration</dt>
            <dd class="mt-1 truncate text-3xl font-semibold tracking-tight">
                @if ($latestRegistration)
                    <time datetime="{{ $latestRegistration->created_at->toIso8601String() }}" title="{{ $latestRegistration->created_at->format('M j, Y g:i A') }}">
                        {{ $latestRegistration->created_at->diffForHumans(short: true) }}
                    </time>
                @else
                    —
                @endif
            </dd>
        </div>
    </dl>

    <div class="mt-6 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <section class="min-w-0">
            <form method="GET" action="{{ route('admin.registrations.index') }}" class="flex flex-col gap-3 sm:flex-row">
                <label for="search" class="sr-only">Search</label>
                <input
                    id="search"
                    name="search"
                    type="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Search name, number or GN division"
                    class="h-11 w-full min-w-0 rounded-lg border sm:flex-1 border-slate-300 bg-white px-3 text-sm focus:border-summit-red focus:ring-4 focus:ring-summit-red/15 focus:outline-none"
                >

                <label for="gn_division" class="sr-only">GN division</label>
                <select
                    id="gn_division"
                    name="gn_division"
                    data-auto-submit
                    class="h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-summit-red focus:ring-4 focus:ring-summit-red/15 focus:outline-none sm:max-w-56"
                >
                    <option value="">All GN divisions</option>
                    @foreach ($divisionCounts as $division)
                        <option value="{{ $division->gn_division }}" @selected($filters['gn_division'] === $division->gn_division)>
                            {{ $division->gn_division }} ({{ $division->total }})
                        </option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="h-11 flex-1 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-700 sm:flex-none">Search</button>
                    @if ($isFiltered)
                        <a href="{{ route('admin.registrations.index') }}" class="flex h-11 items-center rounded-lg px-3 text-sm font-medium text-slate-600 hover:bg-slate-200">Clear</a>
                    @endif
                </div>
            </form>

            <div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="relative overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                            <tr>
                                <th scope="col" class="px-4 py-3">Ref</th>
                                <th scope="col" class="px-4 py-3">Full name</th>
                                <th scope="col" class="px-4 py-3">WhatsApp</th>
                                <th scope="col" class="px-4 py-3">GN division</th>
                                <th scope="col" class="px-4 py-3">Registered</th>
                                <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($registrations as $registration)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 font-mono text-xs whitespace-nowrap text-slate-500">{{ $registration->reference }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ $registration->full_name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <a href="{{ $registration->whatsapp_link }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-slate-700 hover:text-green-700 hover:underline">
                                            <x-icons.whatsapp class="size-4 text-whatsapp" />
                                            {{ $registration->local_whatsapp_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.registrations.index', ['gn_division' => $registration->gn_division]) }}" class="inline-block rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-200">
                                            {{ $registration->gn_division }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                                        <time datetime="{{ $registration->created_at->toIso8601String() }}">{{ $registration->created_at->format('M j, g:i A') }}</time>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('admin.registrations.destroy', $registration) }}" data-confirm="Delete {{ $registration->full_name }}'s registration ({{ $registration->reference }})? This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600" title="Delete registration">
                                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="size-4" aria-hidden="true">
                                                    <path d="M3.5 5.5h13M8 5.5V4a1 1 0 011-1h2a1 1 0 011 1v1.5m-6.5 0l.7 10.1a1.5 1.5 0 001.5 1.4h4.6a1.5 1.5 0 001.5-1.4l.7-10.1" />
                                                </svg>
                                                <span class="sr-only">Delete {{ $registration->full_name }}</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-14 text-center text-slate-500">
                                        @if ($isFiltered)
                                            No registrations match your search.
                                        @else
                                            No registrations yet. Share the form link to get started.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($registrations->hasPages())
                    <div class="border-t border-slate-200 px-4 py-3">
                        {{ $registrations->links() }}
                    </div>
                @endif
            </div>
        </section>

        <aside class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold">Registrations by GN division</h2>

            @if ($topDivisions->isEmpty())
                <p class="mt-3 text-sm text-slate-500">Divisions will appear here as people register.</p>
            @else
                <ul class="mt-4 flex flex-col gap-1">
                    @foreach ($topDivisions as $division)
                        <li>
                            <a
                                href="{{ route('admin.registrations.index', ['gn_division' => $division->gn_division]) }}"
                                @class([
                                    'group block rounded-lg px-2 py-2 hover:bg-slate-50',
                                    'bg-slate-50' => $filters['gn_division'] === $division->gn_division,
                                ])
                            >
                                <span class="flex items-baseline justify-between gap-3 text-sm">
                                    <span class="truncate font-medium text-slate-800 group-hover:text-slate-950">{{ $division->gn_division }}</span>
                                    <span class="text-slate-500 tabular-nums">{{ number_format($division->total) }}</span>
                                </span>
                                <span class="mt-1.5 block h-1.5 rounded-full bg-slate-100">
                                    <span class="block h-full rounded-full bg-summit-blue" style="width: {{ round($division->total / $largestDivisionCount * 100, 1) }}%"></span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if ($divisionCounts->count() > $topDivisions->count())
                    <p class="mt-3 px-2 text-xs text-slate-500">
                        Showing the top {{ $topDivisions->count() }} of {{ $divisionCounts->count() }} divisions. Use the filter to see the rest.
                    </p>
                @endif
            @endif
        </aside>
    </div>
</x-layouts.admin>
