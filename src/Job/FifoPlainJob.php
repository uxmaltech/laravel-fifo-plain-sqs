<?php

namespace Uxmal\FifoPlainSqs\Job;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class FifoPlainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    private string $traceId;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $data)
    {
        $this->traceId = Str::orderedUuid()->toString();
    }

    /**
     * Execute the job.
     */
    public function handle(Job $job, array $data): void
    {
        //
    }

    public function getPayload()
    {
        $traceId = $this->traceId ?? Str::orderedUuid()->toString();

        return [
            'message_body' => $this->data,
            'message_attributes' => [
                'TraceId' => [
                    'DataType' => 'String',
                    'StringValue' => $traceId,
                ],
            ],
        ];
    }
}
