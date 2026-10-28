<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class TokenRepository
{
    //to create a new login session
    public function create(int $userId, string $tokenHash, string $expiresAt): void
    {
        DB::table('auth_tokens')->insert([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
    }

    /** Returns how many rows were deleted: 1 if the token existed, 0 if not. */
    public function deleteByHash(string $tokenHash): int
    {
        return DB::table('auth_tokens')->where('token_hash', $tokenHash)->delete();
    }
}