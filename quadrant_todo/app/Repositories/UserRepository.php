<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class UserRepository
{
    //commad to find a user by email
    public function findByEmail(string $email): ?object
    {
        return DB::table('users')->where('email', $email)->first();
    }

    //command to append a new recoer in the user table with the email and hashed pass..
    public function create(string $email, string $passwordHash): int
    {
        return DB::table('users')->insertGetId([
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);
    }
}