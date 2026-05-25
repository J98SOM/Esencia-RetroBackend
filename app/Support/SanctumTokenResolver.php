<?php

namespace App\Support;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class SanctumTokenResolver
{
    public static function resolveUser(?string $plainTextToken): ?User
    {
        if (! $plainTextToken || ! str_contains($plainTextToken, '|')) {
            return null;
        }

        [$tokenId, $tokenValue] = explode('|', $plainTextToken, 2);

        if (! is_numeric($tokenId) || $tokenValue === '') {
            return null;
        }

        $token = PersonalAccessToken::find($tokenId);
        if (! $token) {
            return null;
        }

        if (! hash_equals($token->token, hash('sha256', $tokenValue))) {
            return null;
        }

        return $token->tokenable instanceof User ? $token->tokenable : null;
    }
}
