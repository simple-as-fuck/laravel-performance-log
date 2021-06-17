<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Middleware;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Log\LogManager;
use SimpleAsFuck\LaravelPerformanceLog\Service\Stopwatch;

final class PerformanceMiddleware
{
    private Stopwatch $stopwatch;
    private Repository $config;
    private LogManager $logManager;

    public function __construct(Stopwatch $stopwatch, Repository $config, LogManager $logManager)
    {
        $this->stopwatch = $stopwatch;
        $this->config = $config;
        $this->logManager = $logManager;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function handle($request, \Closure $next, float $threshold = null)
    {
        $threshold ??= $this->config->get('app.performance.http.slow_request_threshold');
        if ($threshold === null) {
            return $next($request);
        }

        $measurement = $this->stopwatch->startMeasurement();

        $response = $next($request);

        $logger = $this->logManager->channel($this->config->get('app.performance.log_channel'));
        if ($threshold == 0 && $this->config->get('app.debug')) {
            $time = $this->stopwatch->check($measurement, $threshold);
            $logger->debug('Http request: "'.$request->method().'" "'.$request->fullUrl().'" time: '.$time.'ms');
            return $response;
        }
        $this->stopwatch->check($measurement, $threshold, fn (float $time) => $logger->warning('Http request: "'.$request->method().'" "'.$request->fullUrl().'" is to slow: '.$time.'ms threshold: '.$threshold.'ms'));

        return $response;
    }
}
