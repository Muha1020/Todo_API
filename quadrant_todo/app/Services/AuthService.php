<?php

namespace App\Services;

use App\Exceptions\ApiExceptions;
use App\Repositories\TokenRepository;
use App\Repositories\UserRepository;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Carbon;

class AuthService
{
    //constants used everyhere
    private const MAX_FAILED_LOGINS = 10;
    private const LOCKOUT_SECONDS = 900;       // 15 minutes
    private const TOKEN_LIFETIME_DAYS = 30;

    public function __construct(
        private UserRepository $users,
        private TokenRepository $tokens,
    ) {}

    //registration service
    public function register(string $email, string $password): array
    {
        if ($this->users->findByEmail($email) !== null) {
            throw ApiExceptions::conflict('The email is already registered.');
        }

        try {
            $id = $this->users->create($email, password_hash($password, PASSWORD_DEFAULT));
        } catch (UniqueConstraintViolationException) {
            // Two requests with the same email can race past the check above.
            // The UNIQUE index in the database stops the second one.
            throw ApiExceptions::conflict('The email is already registered.');
        }

        return ['id' => $id, 'email' => $email];
    }

    //login service, yea
    public function login(string $email, string $password): array
    {
        $key = 'login-failures:'.sha1(strtolower($email));

        if (RateLimiter::tooManyAttempts($key, self::MAX_FAILED_LOGINS)) {
            throw ApiExceptions::tooManyRequests();
        }

        $user = $this->users->findByEmail($email);

        // Always run one password check, even for an unknown email, so the
        // response time does not reveal whether the email exists.
        $hash = $user?->password_hash ?? password_hash('placeholder-password', PASSWORD_DEFAULT);
        $valid = password_verify($password, $hash) && $user !== null;

        if (! $valid) {
            RateLimiter::hit($key, self::LOCKOUT_SECONDS);
            throw ApiExceptions::invalidCredentials();
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = now()->utc()->addDays(self::TOKEN_LIFETIME_DAYS);

        $this->tokens->create(
            $user->id,
            $this->hashToken($token),
            $expiresAt->toDateTimeString(),
        );

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601ZuluString(),
        ];
    }

    //logout service
    public function logout(?string $plainToken): void
    {
        if ($plainToken === null || $plainToken === '') {
            throw ApiExceptions::unauthorized();
        }

        $deleted = $this->tokens->deleteByHash($this->hashToken($plainToken));

        if ($deleted === 0) {
            throw ApiExceptions::unauthorized();
        }
    }
    private function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    //middleware service
    public function authenticate(?string $plainToken): int
    {

    if ($plainToken === null || $plainToken === ''){
        throw ApiExceptions::unauthorized();
    }

    $row = $this->tokens->findByHash($this->hashToken($plainToken));

    if ($row === null) {
        throw ApiExceptions::unauthorized();
    }

    //expiry. A dead row is deleted, then the request fails.
    if (Carbon::parse($row->expires_at, 'UTC')->isPast()) {
        $this->tokens->deleteById((int) $row->id);
        throw ApiExceptions::unauthorized();
    }

    // The caller attaches this to the request (stage 5)
    return (int) $row->user_id;
    }
}