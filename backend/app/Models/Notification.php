<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public static function keUser(?int $userId, string $type, string $title, string $message, array $data = []): void
    {
        if (! $userId) return; // pelanggan walk-in tanpa akun
        self::create(['user_id' => $userId, 'type' => $type, 'title' => $title, 'message' => $message, 'data' => $data]);
    }

    public static function keAdmin(string $type, string $title, string $message, array $data = []): void
    {
        User::where('role', 'admin')->pluck('id')
            ->each(fn ($id) => self::keUser($id, $type, $title, $message, $data));
    }
}
