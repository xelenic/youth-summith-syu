<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationController extends Controller
{
    /**
     * List registrations with search, a GN division filter, and summary figures.
     */
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);

        $divisionCounts = Registration::query()
            ->selectRaw('gn_division, count(*) as total')
            ->groupBy('gn_division')
            ->orderByDesc('total')
            ->orderBy('gn_division')
            ->get();

        return view('admin.registrations.index', [
            'registrations' => $this->filteredQuery($filters)->orderByDesc('id')->paginate(25)->withQueryString(),
            'filters' => $filters,
            'divisionCounts' => $divisionCounts,
            'totalCount' => (int) $divisionCounts->sum('total'),
            'todayCount' => Registration::query()->where('created_at', '>=', today())->count(),
            'latestRegistration' => Registration::query()->latest('id')->first(),
        ]);
    }

    /**
     * Download the (filtered) registrations as a CSV file that opens cleanly in Excel.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->filteredQuery($this->validatedFilters($request));

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            // A UTF-8 byte order mark so Excel shows Sinhala and Tamil text correctly.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Reference', 'Full Name', 'WhatsApp (local)', 'WhatsApp (international)', 'GN Division', 'Registered At'], escape: '');

            foreach ($query->lazyById(500) as $registration) {
                fputcsv($handle, [
                    $registration->reference,
                    $this->spreadsheetSafe($registration->full_name),
                    $registration->local_whatsapp_number,
                    ltrim($registration->whatsapp_number, '+'),
                    $this->spreadsheetSafe($registration->gn_division),
                    $registration->created_at->format('Y-m-d H:i'),
                ], escape: '');
            }

            fclose($handle);
        }, 'syu-youth-summit-registrations-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Delete a registration (e.g. a duplicate or spam entry).
     */
    public function destroy(Registration $registration): RedirectResponse
    {
        $registration->delete();

        return back()->with('status', "Deleted {$registration->full_name}'s registration ({$registration->reference}).");
    }

    /**
     * @return array{search: string, gn_division: string}
     */
    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'gn_division' => ['nullable', 'string', 'max:100'],
        ]);

        return [
            'search' => trim($validated['search'] ?? ''),
            'gn_division' => $validated['gn_division'] ?? '',
        ];
    }

    /**
     * @param  array{search: string, gn_division: string}  $filters
     * @return Builder<Registration>
     */
    private function filteredQuery(array $filters): Builder
    {
        return Registration::query()
            ->search($filters['search'])
            ->when($filters['gn_division'] !== '', fn (Builder $query) => $query->where('gn_division', $filters['gn_division']));
    }

    /**
     * Stop spreadsheet apps from treating a cell as a formula.
     */
    private function spreadsheetSafe(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
