<?php

namespace App\Http\Api;

use Illuminate\Http\JsonResponse;

/**
 * The only two shapes /api/v1 speaks.
 *
 * Success is {data, meta}. Failure is {message, errors}. Empty meta and
 * errors are objects, never arrays, so clients can read them the same way
 * on every response.
 */
final class V1Response
{
    public static function ok(mixed $data, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => $meta === [] ? new \stdClass : $meta,
        ], $status);
    }

    public static function error(string $message, array $errors = [], int $status = 400): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors === [] ? new \stdClass : $errors,
        ], $status);
    }
}
