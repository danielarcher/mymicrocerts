<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LogRequestInfo
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info('Request: ' . $request->url(), $this->obfuscateSensitiveInfo($request->json()->all()));

        /** @var Response $response */
        $response = $next($request);

        Log::info(
            'Response',
            array_merge(
                $this->obfuscateSensitiveInfo(json_decode($response->content(), true)),
                ['statusCode' => $response->status()]
            )
        );

        return $response->withHeaders(['Content-Type' => 'application/vnd.api+json']);
    }

    public function obfuscateSensitiveInfo(?array $data): array
    {
        if (!is_array($data)) {
            return [];
        }
        array_walk($data, function (&$value, $key) {
            if (in_array($key, ['password', 'token', 'key'])) {
                $value = '***';
            }
        });

        return $data;
    }
}
