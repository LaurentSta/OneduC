<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class CodeGeneratorService
{
    public static function generateUniqueAccessCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (User::where('code_acces', $code)->exists());

        return $code;
    }
}
