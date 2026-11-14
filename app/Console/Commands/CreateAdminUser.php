<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('admin:create
    {--name= : The admin\'s display name}
    {--email= : The admin\'s email address}
    {--password= : The password (you will be prompted if omitted)}')]
#[Description('Create an admin who can sign in at /admin, or reset an existing admin\'s password')]
class CreateAdminUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->option('email') ?? text(label: 'Email address', required: true);
        $existingAdmin = User::query()->where('email', $email)->first();

        $name = $this->option('name')
            ?? $existingAdmin?->name
            ?? text(label: 'Name', default: 'Admin', required: true);

        $password = $this->option('password')
            ?? password(label: 'Password', hint: 'At least 8 characters.', required: true);

        $validator = Validator::make(compact('name', 'email', 'password'), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $admin = User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password],
        );

        $this->components->info($admin->wasRecentlyCreated
            ? "Admin {$email} created. Sign in at ".route('login')
            : "Password updated for {$email}.");

        return self::SUCCESS;
    }
}
