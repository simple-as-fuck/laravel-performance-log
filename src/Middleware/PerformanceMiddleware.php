<?php

declare(strict_types=1);

namespace SimpleAsFuck\LaravelPerformanceLog\Middleware;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Log\LogManager;
use SimpleAsFuck\LaravelPerformanceLog\Service\Stopwatch;
use SimpleAsFuck\Validator\Factory\Validator;

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
        $threshold ??= Validator::make($this->config->get('performance_log.http.slow_request_threshold'))->float()->min(0)->nullable();
        if ($threshold === null) {
            return $next($request);
        }

        $measurement = $this->stopwatch->startMeasurement();

        $response = $next($request);

        $logger = $this->logManager->channel(Validator::make($this->config->get('performance_log.log_channel'))->string()->nullable());
        if ($threshold == 0 && Validator::make($this->config->get('app.debug'))->bool()->notNull()) {
            $time = $this->stopwatch->check($measurement, $threshold);
            $logger->debug('Http request time: '.$time.'ms method: "'.$request->method().'" url: "'.$request->fullUrl().'" pid: '.\getmypid());
            return $response;
        }
        $this->stopwatch->check($measurement, $threshold, fn (float $time) => $logger->warning('Http request is too slow: '.$time.'ms method: "'.$request->method().'" url: "'.$request->fullUrl().'" threshold: '.$threshold.'ms pid: '.\getmypid()));

        return $response;
    }
}
