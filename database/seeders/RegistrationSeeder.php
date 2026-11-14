<?php

namespace Database\Seeders;

use App\Models\Registration;
use Illuminate\Database\Seeder;

/**
 * Sample registrations for local development only.
 *
 * Not called from DatabaseSeeder; run it explicitly with:
 * php artisan db:seed --class=RegistrationSeeder
 */
class RegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Registration::factory()->count(40)->create();
    }
}
