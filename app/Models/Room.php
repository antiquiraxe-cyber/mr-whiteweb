<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Room extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'has_undercover' => 'boolean',
        ];
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class)->orderBy('turn_order');
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public static function generateCode(): string
    {
        do {
            $code = Str::upper(Str::random(4));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
