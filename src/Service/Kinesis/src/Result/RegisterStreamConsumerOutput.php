<?php

namespace AsyncAws\Kinesis\Result;

use AsyncAws\Core\Response;
use AsyncAws\Core\Result;
use AsyncAws\Kinesis\ValueObject\Consumer;

class RegisterStreamConsumerOutput extends Result
{
    /**
     * An object that represents the details of the consumer you registered. When you register a consumer, it gets an ARN
     * that is generated by Kinesis Data Streams.
     */
    private $consumer;

    public function getConsumer(): Consumer
    {
        $this->initialize();

        return $this->consumer;
    }

    protected function populateResult(Response $response): void
    {
        $data = $response->toArray();

        $this->consumer = new Consumer([
            'ConsumerName' => (string) $data['Consumer']['ConsumerName'],
            'ConsumerARN' => (string) $data['Consumer']['ConsumerARN'],
            'ConsumerStatus' => (string) $data['Consumer']['ConsumerStatus'],
            'ConsumerCreationTimestamp' => /** @var \DateTimeImmutable $d */ $d = \DateTimeImmutable::createFromFormat('U.u', sprintf('%.6F', $data['Consumer']['ConsumerCreationTimestamp'])),
        ]);
    }
}
