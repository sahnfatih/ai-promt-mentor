<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        if (! User::where('email', 'admin@promptmentor.test')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@promptmentor.test',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]);
        }
    }

    public function down(): void
    {
        User::where('email', 'admin@promptmentor.test')->delete();
    }
};

