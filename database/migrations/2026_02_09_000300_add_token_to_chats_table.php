<?php

use App\Models\Chat;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->uuid('token')->nullable()->unique()->after('id');
        });

        // Mevcut sohbetler için benzersiz token üret
        Chat::whereNull('token')->chunkById(100, function ($chats) {
            foreach ($chats as $chat) {
                $chat->token = Str::uuid()->toString();
                $chat->save();
            }
        });
    }

    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
};

