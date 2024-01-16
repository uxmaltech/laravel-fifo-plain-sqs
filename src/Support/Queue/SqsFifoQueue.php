<?php

namespace Uxmal\FifoPlainSqs\Support\Queue;

use Dusterio\PlainSqs\Sqs\Queue;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Jobs\SqsJob;

class SqsFifoQueue extends Queue
{
    protected function createPayload($job, $data = '', $queue = null)
    {
        return $job->getPayload();
    }

    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $response = $this->sqs->sendMessage([
            'MessageAttributes' => $payload['message_attributes'] ?? [],
            'QueueUrl' => $this->getQueue($queue),
            'MessageBody' => json_encode($payload['message_body']),
            'MessageGroupId' => uniqid(),
            'MessageDeduplicationId' => uniqid(),
        ]);

        return $response->get('MessageId');
    }

    private function modifyPayload($payload, $class)
    {
        if (! is_array($payload)) {
            $payload = json_decode($payload, true);
        }

        $body = json_decode($payload['Body'], true);

        $body = [
            'job' => $class.'@handle',
            'data' => isset($body['data']) ? $body['data'] : $body,
            'uuid' => $payload['MessageId'],
            'traceId' => $payload['MessageAttributes']['TraceId']['StringValue'] ?? '',
        ];

        $payload['Body'] = json_encode($body);

        return $payload;
    }

    public function pop($queue = null): SqsJob|Job|null
    {
        $queue = $this->getQueue($queue);

        $response = $this->sqs->receiveMessage([
            'QueueUrl' => $this->getQueue($queue),
            'AttributeNames' => ['ApproximateReceiveCount'],
            'MessageAttributeNames' => ['All'],
        ]);

        if (isset($response['Messages']) && count($response['Messages']) > 0) {
            $queueId = explode('/', $queue);
            $queueId = array_pop($queueId);

            $class = (array_key_exists($queueId, $this->container['config']->get('sqs-plain.handlers')))
                ? $this->container['config']->get('sqs-plain.handlers')[$queueId]
                : $this->container['config']->get('sqs-plain.default-handler');

            $response = $this->modifyPayload($response['Messages'][0], $class);

            if (preg_match(
                '/(5\.[4-8]\..*)|(6\.[0-9]*\..*)|(7\.[0-9]*\..*)|(8\.[0-9]*\..*)|(9\.[0-9]*\..*)|(10\.[0-9]*\..*)/',
                $this->container->version())
            ) {
                return new SqsJob($this->container, $this->sqs, $response, $this->connectionName, $queue);
            }

            return new SqsJob($this->container, $this->sqs, $queue, $response);
        }

        return null;
    }
}
