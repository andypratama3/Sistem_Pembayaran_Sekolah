<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * ApiResponse Trait
 *
 * Standardized API response formatting for all controllers.
 * Provides consistent JSON structure for success/error responses.
 *
 * Usage in Controller:
 * class StudentController extends Controller {
 *     use ApiResponse;
 *
 *     public function show($id) {
 *         $student = Student::find($id);
 *         return $this->success($student, 'Student retrieved successfully');
 *     }
 * }
 */
trait ApiResponse
{
    /**
     * Backward-compatible alias for older controllers.
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return $this->success($data, $message, $code);
    }

    /**
     * Success response
     */
    protected function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'timestamp' => now(),
        ], $code);
    }

    /**
     * Error response
     */
    protected function error(string $message, $data = null, int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'timestamp' => now(),
        ], $code);
    }

    /**
     * Paginated success response
     */
    protected function paginated($paginator, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
            'timestamp' => now(),
        ], 200);
    }

    /**
     * Created response (201)
     */
    protected function created($data, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Unauthorized response (401)
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, null, 401);
    }

    /**
     * Forbidden response (403)
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, null, 403);
    }

    /**
     * Not found response (404)
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, null, 404);
    }

    /**
     * Validation error response (422)
     */
    protected function validationError($errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, $errors, 422);
    }
}
