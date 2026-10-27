<?php

namespace App\Exceptions;

use RuntimeException;

class ApiExceptions extends RuntimeException
{
    public function __construct(
        public readonly string $errorcode,
        string $message,
        public readonly int $status,
        public readonly ?array $fields = null,
    ){
        parent::__construct($message);
    }

    //validation error exception
    public static function validation(string $message, array $fields): self
    {
        return new self('Validation Error',$message, 400, $fields);
    }

    //for any lack of authorization
      public static function unauthorized(string $message = 'The token is missing, unknown, or expired.'): self
    {
        return new self('UNAUTHORIZED', $message, 401);
    }

    //specifically for invalid credentials
    public static function invalidCredentials(): self
    {
        return new self('INVALID_CREDENTIALS', 'The email or the password is not correct.', 401);
    }

    //for 404s 
    public static function notFound(string $message = 'The todo was not found.'): self
    {
        return new self('NOT_FOUND', $message, 404);
    }

    //for conflicts, 409s
    public static function conflict(string $message): self
    {
        return new self('CONFLICT', $message, 409);
    }
}