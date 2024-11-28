<?php

namespace App\Http\Helpers;

class ApiResponse
{
    /**
     * Response untuk status 200 OK
     *
     * @param string $message
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = null, $message = 'Sucess')
    {
        return response()->json(
            [
                'status' => 200,
                'message' => $message,
                'data' => $data,
            ],
            200,
        );
    }

    /**
     * Response untuk status 201 Created
     *
     * @param string $message
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function created($data = null, $message = 'Created')
    {
        return response()->json(
            [
                'status' => 201,
                'message' => $message,
                'data' => $data,
            ],
            201,
        );
    }

    /**
     * Response untuk status 400 Bad Request
     *
     * @param string $message
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function badRequest($data = null, $message = 'Bad Request')
    {
        return response()->json(
            [
                'status' => 400,
                'message' => $message,
                'data' => $data,
            ],
            400,
        );
    }

    /**
     * Response untuk status 404 Not Found
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public static function notFound($message = 'Not Found')
    {
        return response()->json(
            [
                'status' => 404,
                'message' => $message,
                'data' => null,
            ],
            404,
        );
    }

    /**
     * Response untuk status 401 Not Found
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public static function unauthorized($message = 'Unauthorized')
    {
        return response()->json(
            [
                'status' => 401,
                'message' => $message,
                'data' => null,
            ],
            401,
        );
    }

    /**
     * Response untuk status 403 Not Found
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public static function forbidden($message = 'Forbidden')
    {
        return response()->json(
            [
                'status' => 403,
                'message' => $message,
                'data' => null,
            ],
            403,
        );
    }
}
