<?php

/**
 * List of plain SQS queues and their corresponding handling classes
 */

return [
    'handlers' => [
        'default.fifo' => App\Jobs\DefaultHandlerJob::class,
    ],

    'default-handler' => App\Jobs\DefaultHandlerJob::class,
];
