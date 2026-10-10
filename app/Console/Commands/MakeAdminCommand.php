<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MakeAdminCommand extends Command
{
    protected $signature = 'make:admin {username?} {password?}';
    protected $description = 'Creates the initial admin user';

    public function handle()
    {
        $username = $this->argument('username') ?? env('ADMIN_USERNAME', 'admin');
        $password = $this->argument('password') ?? env('ADMIN_PASSWORD', 'admin123');

        if (User::where('username', strtolower($username))->exists()) {
            $this->error('User already exists.');
            return;
        }

        User::create([
            'id' => Str::uuid()->toString(),
            'username' => strtolower($username),
            'password_hash' => Hash::make($password),
            'role' => 'admin',
        ]);

        $this->info("Admin user {$username} created successfully.");
    }
}
