<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProfileJsonResponse
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if ($response instanceof JsonResponse && $request->has('_debug') && app('debugbar')->isEnabled()) {
            $response->setData($response->getData(true) + [
                    '_debugbar' => Arr::only(app('debugbar')->getData(), 'queries'),
                ]);
        }

        return $response;
    }
}
