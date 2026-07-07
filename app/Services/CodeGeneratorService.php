<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class CodeGeneratorService
{
    private const UNAMBIGUOUS_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function generateUniqueAccessCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (User::where('code_acces', $code)->exists());

        return $code;
    }

    public static function generateUniqueCode(string $modelClass, string $column = 'access_code', int $length = 6): string
    {
        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= self::UNAMBIGUOUS_ALPHABET[random_int(0, strlen(self::UNAMBIGUOUS_ALPHABET) - 1)];
            }
        } while ($modelClass::query()->where($column, $code)->exists());

        return $code;
    }
}
