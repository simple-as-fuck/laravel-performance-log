<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Log\LogManager;
use SimpleAsFuck\LaravelPerformanceLog\Model\Measurement;
use SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig;
use SimpleAsFuck\LaravelPerformanceLog\Service\Stopwatch;

final class PerformanceMiddleware
{
    public function __construct(
        private readonly Stopwatch $stopwatch,
        private readonly LogManager $logManager,
        private readonly PerformanceLogConfig $performanceLogConfig
    ) {
    }

    /**
     * @param Request $request
     * @param \Closure(Request): Response $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        $measurement = $this->stopwatch->startMeasurement();
        $this->performanceLogConfig->restoreSlowRequestThreshold();

        $response = $next($request);

        $threshold = $this->performanceLogConfig->getSlowRequestThreshold();
        $this->performanceLogConfig->restoreSlowRequestThreshold();

        $this->log($measurement, $request, $threshold);
        return $response;
    }

    /**
     * @param float|null $threshold value in milliseconds
     */
    private function log(Measurement $measurement, Request $request, ?float $threshold): void
    {
        if ($threshold === null) {
            return;
        }

        $logger = $this->logManager->channel($this->performanceLogConfig->getLogChannelName());

        if ($threshold === 0.0 && $this->performanceLogConfig->isDebugEnabled()) {
            $time = $this->stopwatch->check($measurement, $threshold);
            $logger->debug('Http request time: '.$time.'ms method: "'.$request->method().'" url: "'.$request->fullUrl().'" pid: '.\getmypid());
            return;
        }

        $this->stopwatch->check($measurement, $threshold, fn (float $time) => $logger->warning('Http request is too slow: '.$time.'ms method: "'.$request->method().'" url: "'.$request->fullUrl().'" threshold: '.$threshold.'ms pid: '.\getmypid()));
    }
}
