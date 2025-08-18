<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Middleware;

use Illuminate\Http\Request;
use SimpleAsFuck\PerformanceLog\Middleware\LaravelMiddleware;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated use SimpleAsFuck\PerformanceLog\Middleware\LaravelMiddleware from composer package: simple-as-fuck/php-performance-log
 */
final readonly class PerformanceMiddleware
{
    public function __construct(
        private LaravelMiddleware $middleware,
    ) {
    }

    /**
     * @param Request $request
     * @param \Closure(Request): Response $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        return $this->middleware->handle($request, $next);
    }
}
