<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler; 
use Illuminate\Database\QueryException;   
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException; 
use Throwable; 
use Illuminate\Database\Eloquent\ModelNotFoundException;  
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Add custom logic for reporting specific exceptions if needed
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        // Check if the request is an API request
        if ($request->expectsJson()) { 
            $responseFormat = [
                "code" => 500,
                "status" => false,
                "message" => "An error occurred",
                "data" => null,
                "errors" => [
                    'error'=> $exception->getMessage()
                ]
            ];
    
            if ($exception instanceof ValidationException) {
                return response()->json([
                    "code" => 422,
                    "status" => false,
                    "message" => "Validation errors",
                    "data" => null,
                    "errors" => $exception->validator->errors()
                ], 422);
            }
    
            if ($exception instanceof QueryException) {
                if (strpos($exception->getMessage(), 'Cannot delete or update a parent row: a foreign key constraint fails') !== false) {
                    Log::error('Foreign key constraint violation: ' . $exception->getMessage());
                    return response()->json(array_merge($responseFormat, [
                        "code" => 400,
                        "message" => "Unable to delete this record due to a foreign key constraint.",
                        "errors" => "The requested action cannot be completed because there are dependent records in other tables."
                    ]), 400);
                }
            }
    
            if ($exception instanceof ModelNotFoundException) {
                Log::error('404 Not Found: ' . $exception->getMessage());
                return response()->json(array_merge($responseFormat, [
                    "code" => 404,
                    "message" => "The requested resource could not be found.",
                    "errors" => "Model Not Found"
                ]), 404);
            }
    
            if ($exception instanceof NotFoundHttpException) {
                Log::error('404 Not Found: ' . $exception->getMessage());
                return response()->json(array_merge($responseFormat, [
                    "code" => 404,
                    "message" => "The requested route could not be found.",
                    "errors" => "Not Found"
                ]), 404);
            }
    
            if ($exception instanceof AuthenticationException) {
                return response()->json(array_merge($responseFormat, [
                    "code" => 401,
                    "message" => "You are not authenticated to access this resource.",
                    "errors" => "Unauthorized"
                ]), 401);
            }
    
            if ($exception instanceof AuthorizationException) {
                return response()->json(array_merge($responseFormat, [
                    "code" => 403,
                    "message" => "You do not have permission to perform this action.",
                    "errors" => "Forbidden"
                ]), 403);
            }
    
            if ($exception instanceof MethodNotAllowedHttpException) {
                return response()->json(array_merge($responseFormat, [
                    "code" => 405,
                    "message" => "The used HTTP method is not allowed for this route.",
                    "errors" => "Method Not Allowed"
                ]), 405);
            }
    
            if ($exception instanceof RouteNotFoundException) {
                return response()->json(array_merge($responseFormat, [
                    "code" => 401,
                    "message" => "You must be authenticated to access this resource.",
                    "errors" => "Unauthorized"
                ]), 401);
            }
    
            if ($exception instanceof QueryException) {
                return response()->json(array_merge($responseFormat, [
                    "code" => 500,
                    "message" => "A database error occurred. Please check your query or try again later.",
                    "errors" => [
                        'sql'=>$exception->getMessage()]
                ]), 500);
            }
    
            if ($exception instanceof ThrottleRequestsException) {
                return response()->json(array_merge($responseFormat, [
                    "code" => 429,
                    "message" => "You have exceeded the allowed number of requests. Please try again later.",
                    "errors" => "Too Many Requests"
                ]), 429);
            }
    
            if ($exception instanceof TokenMismatchException) {
                return response()->json(array_merge($responseFormat, [
                    "code" => 419,
                    "message" => "Your session has expired. Please refresh the token and try again.",
                    "errors" => "Token Mismatch"
                ]), 419);
            }
    
            return response()->json($responseFormat, 500);
        }
    
        // If it's a web request, fallback to default Laravel behavior
        return parent::render($request, $exception);
    }
    
    
}