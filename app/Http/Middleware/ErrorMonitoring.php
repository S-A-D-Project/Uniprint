<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ErrorMonitoring
{
    /**
     * Handle an incoming request and monitor for errors.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        try {
            $response = $next($request);

            // Log performance metrics for slow requests
            $executionTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
            $memoryUsage = memory_get_usage(true) - $startMemory;

            if ($executionTime > 1000) { // Log requests taking more than 1 second
                Log::warning('Slow request detected', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'execution_time_ms' => round($executionTime, 2),
                    'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
                    'user_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            // Log security-relevant events
            $this->logSecurityEvents($request, $response);

            return $response;

        } catch (\Exception $e) {
            // Log detailed error information
            Log::error('Request error occurred', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_data' => $this->sanitizeRequestData($request),
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            // Re-throw the exception to maintain normal error handling
            throw $e;
        }
    }

    /**
     * Log security-relevant events
     */
    private function logSecurityEvents(Request $request, Response $response): void
    {
        $statusCode = $response->getStatusCode();

        // Log authentication failures
        if ($statusCode === 401) {
            Log::warning('Authentication failure', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'attempted_user' => $request->input('username') ?? $request->input('email'),
            ]);
        }

        // Log authorization failures
        if ($statusCode === 403) {
            Log::warning('Authorization failure', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Log validation errors that might indicate attacks
        if ($statusCode === 422) {
            $suspiciousPatterns = [
                'script', 'javascript', 'eval', 'exec', 'system',
                'DROP TABLE', 'SELECT * FROM', 'UNION SELECT',
                '../', '..\\', '/etc/passwd', 'cmd.exe'
            ];

            $requestData = json_encode($request->all());
            foreach ($suspiciousPatterns as $pattern) {
                if (stripos($requestData, $pattern) !== false) {
                    Log::alert('Potential security attack detected', [
                        'pattern' => $pattern,
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'user_id' => Auth::id(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'request_data' => $this->sanitizeRequestData($request),
                    ]);
                    break;
                }
            }
        }

        // Log rate limiting violations
        if ($statusCode === 429) {
            Log::warning('Rate limit exceeded', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
    }

    /**
     * Sanitize request data for logging (remove sensitive information)
     */
    private function sanitizeRequestData(Request $request): array
    {
        $data = $request->all();
        
        // Remove sensitive fields
        $sensitiveFields = [
            'password', 'password_confirmation', 'current_password',
            'token', 'api_key', 'secret', 'private_key',
            'credit_card', 'ssn', 'social_security'
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        // Limit data size to prevent log bloat
        $jsonData = json_encode($data);
        if (strlen($jsonData) > 2048) {
            return ['message' => 'Request data too large for logging', 'size' => strlen($jsonData)];
        }

        return $data;
    }
}
