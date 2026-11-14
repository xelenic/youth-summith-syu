<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    /**
     * Show the registration form, or the confirmation after a successful registration.
     */
    public function create(): View
    {
        $registration = session()->has('registered_id')
            ? Registration::find(session('registered_id'))
            : null;

        return view('registrations.create', [
            'registration' => $registration,
            'gnDivisions' => $this->gnDivisionSuggestions(),
        ]);
    }

    /**
     * Store a new registration.
     */
    public function store(StoreRegistrationRequest $request): RedirectResponse
    {
        $registration = Registration::create($request->safe()->except('terms'));

        return to_route('registrations.create')->with('registered_id', $registration->id);
    }

    /**
     * Configured divisions plus any division entered by at least two registrants.
     *
     * @return list<string>
     */
    private function gnDivisionSuggestions(): array
    {
        $popular = Registration::query()
            ->select('gn_division')
            ->groupBy('gn_division')
            ->havingRaw('count(*) >= 2')
            ->pluck('gn_division');

        return collect(config('summit.gn_divisions'))
            ->merge($popular)
            ->unique(fn (string $division): string => mb_strtolower($division))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }
}
