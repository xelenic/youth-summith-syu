<?php

use App\Models\Registration;

/**
 * @param  array<string, string>  $overrides
 * @return array<string, string>
 */
function registrationPayload(array $overrides = []): array
{
    return [
        'full_name' => 'Nimal Perera',
        'whatsapp_number' => '077 123 4567',
        'gn_division' => 'Buttala',
        'terms' => '1',
        ...$overrides,
    ];
}

test('the registration form is shown', function () {
    $this->get(route('registrations.create'))
        ->assertOk()
        ->assertSee('Registration Form')
        ->assertSee('Register Now');
});

test('a person can register and sees a confirmation with their registration number', function () {
    $response = $this->post(route('registrations.store'), registrationPayload([
        'full_name' => '  Nimal   Perera ',
    ]));

    $registration = Registration::sole();

    $response->assertRedirect(route('registrations.create'));
    expect($registration->only('full_name', 'whatsapp_number', 'gn_division'))->toBe([
        'full_name' => 'Nimal Perera',
        'whatsapp_number' => '+94771234567',
        'gn_division' => 'Buttala',
    ]);

    $this->get(route('registrations.create'))
        ->assertSee("You're registered!", false)
        ->assertSee($registration->reference)
        ->assertSee('077 123 4567')
        ->assertDontSee('Register Now');
});

test('WhatsApp numbers are stored in international format however they are typed', function (string $typedNumber) {
    $this->post(route('registrations.store'), registrationPayload(['whatsapp_number' => $typedNumber]))
        ->assertValid();

    expect(Registration::sole()->whatsapp_number)->toBe('+94771234567');
})->with([
    'local' => '0771234567',
    'local with spaces' => '077 123 4567',
    'local with dashes' => '077-123-4567',
    'without leading zero' => '771234567',
    'international' => '+94 77 123 4567',
    'international without plus' => '94771234567',
    'international with 00' => '0094 77 123 4567',
]);

test('all fields and the terms are required', function () {
    $this->post(route('registrations.store'), [])
        ->assertInvalid([
            'full_name' => 'Please enter your full name.',
            'whatsapp_number' => 'Please enter your WhatsApp number.',
            'gn_division' => 'Please enter your GN Division.',
            'terms' => 'Please agree to the terms and conditions.',
        ]);

    expect(Registration::count())->toBe(0);
});

test('numbers that are not Sri Lankan mobile numbers are rejected', function (string $typedNumber) {
    $this->post(route('registrations.store'), registrationPayload(['whatsapp_number' => $typedNumber]))
        ->assertInvalid(['whatsapp_number' => 'Enter a valid Sri Lankan mobile number, e.g. 077 123 4567.']);

    expect(Registration::count())->toBe(0);
})->with([
    'too short' => '077 123',
    'landline' => '011 234 5678',
    'letters' => 'my number',
]);

test('the same WhatsApp number cannot register twice, even when typed differently', function () {
    Registration::factory()->create(['whatsapp_number' => '+94771234567']);

    $this->post(route('registrations.store'), registrationPayload(['whatsapp_number' => '0771234567']))
        ->assertInvalid(['whatsapp_number' => 'This WhatsApp number is already registered.']);

    expect(Registration::count())->toBe(1);
});

test('a GN division typed in a different case reuses the existing spelling', function () {
    Registration::factory()->create(['gn_division' => 'Kumbukkana North']);

    $this->post(route('registrations.store'), registrationPayload(['gn_division' => 'kumbukkana  north']));

    expect(Registration::latest('id')->first()->gn_division)->toBe('Kumbukkana North');
});

test('GN division suggestions include configured divisions and ones entered by several people', function () {
    config(['summit.gn_divisions' => ['Configured Division']]);
    Registration::factory()->count(2)->create(['gn_division' => 'Popular Division']);
    Registration::factory()->create(['gn_division' => 'Rare Division']);

    $this->get(route('registrations.create'))
        ->assertSee('<option value="Configured Division">', false)
        ->assertSee('<option value="Popular Division">', false)
        ->assertDontSee('Rare Division');
});
