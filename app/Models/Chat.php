<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'token',
    ];

    protected static function booted(): void
    {
        static::creating(function (Chat $chat) {
            if (! $chat->token) {
                $chat->token = Str::uuid()->toString();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }
}

