<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Middleware;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Log\LogManager;
use SimpleAsFuck\LaravelPerformanceLog\Model\Measurement;
use SimpleAsFuck\LaravelPerformanceLog\Service\PerformanceLogConfig;
use SimpleAsFuck\LaravelPerformanceLog\Service\Stopwatch;
use SimpleAsFuck\Validator\Factory\Validator;

final class PerformanceMiddleware
{
    private Stopwatch $stopwatch;
    private Repository $config;
    private LogManager $logManager;
    private PerformanceLogConfig $performanceLogConfig;

    public function __construct(
        Stopwatch $stopwatch,
        Repository $config,
        LogManager $logManager,
        PerformanceLogConfig $performanceLogConfig
    ) {
        $this->stopwatch = $stopwatch;
        $this->config = $config;
        $this->logManager = $logManager;
        $this->performanceLogConfig = $performanceLogConfig;
    }

    /**
     * @deprecated $threshold parameter will be removed in the next version
     *
     * @param Request $request
     * @param float|null $threshold value in milliseconds
     * @return Response
     */
    public function handle($request, \Closure $next, float $threshold = null)
    {
        $measurement = $this->stopwatch->startMeasurement();
        $this->performanceLogConfig->restoreSlowRequestThreshold();

        $response = $next($request);

        if ($this->performanceLogConfig->isSlowRequestThresholdTemporary()) {
            $threshold = $this->performanceLogConfig->getSlowRequestThreshold();
        } else {
            $threshold ??= $this->performanceLogConfig->getSlowRequestThreshold();
        }
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

        $logger = $this->logManager->channel(Validator::make($this->config->get('performance_log.log_channel'))->string()->nullable());

        if ($threshold === 0.0 && Validator::make($this->config->get('app.debug'))->bool()->notNull()) {
            $time = $this->stopwatch->check($measurement, $threshold);
            $logger->debug('Http request time: '.$time.'ms method: "'.$request->method().'" url: "'.$request->fullUrl().'" pid: '.\getmypid());
            return;
        }

        $this->stopwatch->check($measurement, $threshold, fn (float $time) => $logger->warning('Http request is too slow: '.$time.'ms method: "'.$request->method().'" url: "'.$request->fullUrl().'" threshold: '.$threshold.'ms pid: '.\getmypid()));
    }
}
