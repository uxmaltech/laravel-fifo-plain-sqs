<?php

namespace Uxmal\FifoPlainSqs;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Uxmal\FifoPlainSqs\Support\Queue\Connectors\SqsFifoConnector;

class SqsFifoPlainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->booted(function () {
            $this->app['queue']->extend('fifo-plain-sqs', function () {
                return new SqsFifoConnector();
            });
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/sqs-plain.php' => config_path('sqs-plain.php'),
        ]);

        Queue::after(function (JobProcessed $event) {
            $event->job->delete();
        });
    }
}
