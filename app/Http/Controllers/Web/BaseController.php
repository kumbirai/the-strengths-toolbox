<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

abstract class BaseController extends Controller
{
    /**
     * Return JSON response for API-like requests
     */
    protected function jsonResponse(array $data, int $status = 200)
    {
        return response()->json($data, $status);
    }

    /**
     * Return error response
     */
    protected function errorResponse(string $message, int $status = 400)
    {
        return response()->json([
            'error' => $message,
        ], $status);
    }

    /**
     * Return success response
     */
    protected function successResponse(array $data = [], ?string $message = null)
    {
        $response = ['success' => true];

        if ($message) {
            $response['message'] = $message;
        }

        if (! empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response);
    }
}
