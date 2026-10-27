<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

//for a successful call
class ApiResponse
{
    public static function success(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json(['success' => true,'data'=> $data], $status);
    }

    //for a response with no content
    public static function noContent(): Response
    {
        return response()->noContent();
    }

    //for an error response
    public static function error(
        string $code,
        string $message,
        int $status,
        ?array $fields = null
    ): JsonResponse{
        $error = ['code'=> $code,'message'=> $message];
        if ($fields !== null) {
            $error['fields'] = $fields;
        }
        return response()->json(['success' => false, 'error' => $error], $status);
    }
}