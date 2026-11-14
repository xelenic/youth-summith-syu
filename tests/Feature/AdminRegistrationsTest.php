<?php

use App\Models\Registration;
use App\Models\User;

test('guests are sent to the sign-in page', function (string $method, Closure $url) {
    $registration = Registration::factory()->create();

    $this->call($method, $url($registration))->assertRedirect(route('login'));

    expect(Registration::count())->toBe(1);
})->with([
    'dashboard' => ['GET', fn () => route('admin.registrations.index')],
    'export' => ['GET', fn () => route('admin.registrations.export')],
    'delete' => ['DELETE', fn (Registration $registration) => route('admin.registrations.destroy', $registration)],
]);

test('an admin can sign in', function () {
    $admin = User::factory()->create();

    $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
        ->assertRedirect(route('admin.registrations.index'));

    $this->assertAuthenticatedAs($admin);
});

test('signing in with a wrong password fails', function () {
    $admin = User::factory()->create();

    $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'wrong-password'])
        ->assertInvalid(['email' => 'These credentials do not match our records.']);

    $this->assertGuest();
});

test('a signed-in admin visiting the sign-in page goes to the dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('login'))
        ->assertRedirect(route('admin.registrations.index'));
});

test('an admin can sign out', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('the dashboard lists registrations with summary figures', function () {
    $registration = Registration::factory()->create([
        'full_name' => 'Kamala Wijesinghe',
        'whatsapp_number' => '+94711234567',
        'gn_division' => 'Buttala',
    ]);
    Registration::factory()->create(['gn_division' => 'Buttala']);
    Registration::factory()->create(['gn_division' => 'Okkampitiya', 'created_at' => now()->subDays(2)]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.registrations.index'))
        ->assertOk()
        ->assertSee('Kamala Wijesinghe')
        ->assertSee($registration->reference)
        ->assertSee('071 123 4567')
        ->assertSee('https://wa.me/94711234567')
        ->assertViewHas('totalCount', 3)
        ->assertViewHas('todayCount', 2)
        ->assertViewHas('divisionCounts', fn ($counts) => $counts->pluck('total', 'gn_division')->all() === ['Buttala' => 2, 'Okkampitiya' => 1]);
});

test('the dashboard can be searched by name or by a locally formatted number', function (string $search) {
    Registration::factory()->create(['full_name' => 'Kamala Wijesinghe', 'whatsapp_number' => '+94711234567']);
    Registration::factory()->create(['full_name' => 'Sunil Bandara', 'whatsapp_number' => '+94767654321']);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.registrations.index', ['search' => $search]))
        ->assertSee('Kamala Wijesinghe')
        ->assertDontSee('Sunil Bandara');
})->with([
    'name' => 'kamala',
    'local number' => '071 123',
]);

test('the dashboard can be filtered by GN division', function () {
    Registration::factory()->create(['full_name' => 'Kamala Wijesinghe', 'gn_division' => 'Buttala']);
    Registration::factory()->create(['full_name' => 'Sunil Bandara', 'gn_division' => 'Okkampitiya']);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.registrations.index', ['gn_division' => 'Okkampitiya']))
        ->assertSee('Sunil Bandara')
        ->assertDontSee('Kamala Wijesinghe');
});

test('registrations can be exported as a CSV that opens in Excel', function () {
    $registration = Registration::factory()->create([
        'full_name' => '=HYPERLINK("http://evil.test")',
        'whatsapp_number' => '+94711234567',
        'gn_division' => 'බුත්තල',
    ]);

    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.registrations.export'));

    $response->assertOk()->assertDownload();
    $lines = explode("\n", trim($response->streamedContent()));

    expect($lines)->toHaveCount(2)
        ->and($lines[0])->toStartWith("\xEF\xBB\xBFReference,")
        ->and($lines[1])->toBe($registration->reference.',"\'=HYPERLINK(""http://evil.test"")","071 123 4567",94711234567,බුත්තල,"'.$registration->created_at->format('Y-m-d H:i').'"');
});

test('the export only includes registrations matching the current filters', function () {
    Registration::factory()->create(['full_name' => 'Kamala Wijesinghe', 'gn_division' => 'Buttala']);
    Registration::factory()->create(['full_name' => 'Sunil Bandara', 'gn_division' => 'Okkampitiya']);

    $csv = $this->actingAs(User::factory()->create())
        ->get(route('admin.registrations.export', ['gn_division' => 'Buttala']))
        ->streamedContent();

    expect($csv)->toContain('Kamala Wijesinghe')->not->toContain('Sunil Bandara');
});

test('an admin can delete a registration', function () {
    $registration = Registration::factory()->create();

    $this->actingAs(User::factory()->create())
        ->from(route('admin.registrations.index'))
        ->delete(route('admin.registrations.destroy', $registration))
        ->assertRedirect(route('admin.registrations.index'))
        ->assertSessionHas('status');

    $this->assertModelMissing($registration);
});
