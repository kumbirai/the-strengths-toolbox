<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

abstract class BaseController extends Controller
{
    /**
     * Return success JSON response
     */
    protected function success(array $data = [], ?string $message = null, int $status = 200)
    {
        $response = [
            'success' => true,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if (! empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Return error JSON response
     */
    protected function error(string $message, int $status = 400, array $errors = [])
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Return validation error response
     */
    protected function validationError(array $errors)
    {
        return $this->error('Validation failed', 422, $errors);
    }
}
