<?php

namespace App\Http\Helpers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ApiResponse
{
    public static function success(
        string $message = 'Request successful.',
        mixed $data = null,
        int $status = HttpResponse::HTTP_OK
    ): JsonResponse {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function created(
        string $message = 'Resource created successfully.',
        mixed $data = null
    ): JsonResponse {
        return self::success($message, $data, HttpResponse::HTTP_CREATED);
    }

    public static function error(
        string $message = 'Something went wrong.',
        mixed $errors = null,
        int $status = HttpResponse::HTTP_BAD_REQUEST
    ): JsonResponse {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    public static function unauthorized(string $message = 'Unauthorized.'): JsonResponse
    {
        return self::error($message, null, HttpResponse::HTTP_UNAUTHORIZED);
    }

    public static function forbidden(string $message = 'Forbidden.'): JsonResponse
    {
        return self::error($message, null, HttpResponse::HTTP_FORBIDDEN);
    }

    public static function notFound(string $message = 'Resource not found.'): JsonResponse
    {
        return self::error($message, null, HttpResponse::HTTP_NOT_FOUND);
    }

    public static function validationError(
        array $errors,
        string $message = 'Validation failed.'
    ): JsonResponse {
        return self::error($message, $errors, HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function serverError(string $message = 'Internal server error.'): JsonResponse
    {
        return self::error($message, null, HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
