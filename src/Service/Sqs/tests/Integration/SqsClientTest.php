<?php

namespace AsyncAws\Sqs\Tests\Integration;

use AsyncAws\Core\Credentials\NullProvider;
use AsyncAws\Sqs\Enum\QueueAttributeName;
use AsyncAws\Sqs\Input\ChangeMessageVisibilityRequest;
use AsyncAws\Sqs\Input\CreateQueueRequest;
use AsyncAws\Sqs\Input\DeleteMessageRequest;
use AsyncAws\Sqs\Input\DeleteQueueRequest;
use AsyncAws\Sqs\Input\GetQueueAttributesRequest;
use AsyncAws\Sqs\Input\GetQueueUrlRequest;
use AsyncAws\Sqs\Input\ListQueuesRequest;
use AsyncAws\Sqs\Input\PurgeQueueRequest;
use AsyncAws\Sqs\Input\ReceiveMessageRequest;
use AsyncAws\Sqs\Input\SendMessageRequest;
use AsyncAws\Sqs\SqsClient;
use PHPUnit\Framework\TestCase;

class SqsClientTest extends TestCase
{
    public function testChangeMessageVisibility()
    {
        $sqs = $this->getClient();

        $sqs->createQueue(['QueueName' => 'foo'])->resolve();
        $fooQueueUrl = $sqs->getQueueUrl(['QueueName' => 'foo'])->getQueueUrl();
        $sqs->purgeQueue(['QueueUrl' => $fooQueueUrl])->resolve();
        $sqs->sendMessage(['QueueUrl' => $fooQueueUrl, 'MessageBody' => 'foo'])->resolve();
        $attributes = $sqs->getQueueAttributes(['QueueUrl' => $fooQueueUrl])->getAttributes();
        self::assertEquals(1, (int) $attributes['ApproximateNumberOfMessages']);
        self::assertEquals(0, (int) $attributes['ApproximateNumberOfMessagesNotVisible']);

        $messages = $sqs->receiveMessage(['QueueUrl' => $fooQueueUrl, 'MaxNumberOfMessages' => 1, 'WaitTimeSeconds' => '2'])->getMessages();
        self::assertCount(1, $messages);
        $attributes = $sqs->getQueueAttributes(['QueueUrl' => $fooQueueUrl])->getAttributes();
        self::assertEquals(0, (int) $attributes['ApproximateNumberOfMessages']);
        self::assertEquals(1, (int) $attributes['ApproximateNumberOfMessagesNotVisible']);

        $input = (new ChangeMessageVisibilityRequest())
            ->setQueueUrl($fooQueueUrl)
            ->setReceiptHandle($messages[0]->getReceiptHandle())
            ->setVisibilityTimeout(0);

        $sqs->changeMessageVisibility($input)->resolve();

        $attributes = $sqs->getQueueAttributes(['QueueUrl' => $fooQueueUrl])->getAttributes();
        self::assertEquals(1, (int) $attributes['ApproximateNumberOfMessages']);
        self::assertEquals(0, (int) $attributes['ApproximateNumberOfMessagesNotVisible']);
    }

    public function testCreateQueue()
    {
        $sqs = $this->getClient();

        $input = (new CreateQueueRequest())
            ->setQueueName('baz')
            ->setAttributes([QueueAttributeName::APPROXIMATE_NUMBER_OF_MESSAGES_DELAYED => '10']);
        $result = $sqs->createQueue($input);

        self::assertStringContainsString('http', $result->getQueueUrl());
        self::assertStringContainsString('baz', $result->getQueueUrl());
    }

    public function testDeleteMessage()
    {
        $sqs = $this->getClient();

        $sqs->createQueue(['QueueName' => 'foo'])->resolve();
        $fooQueueUrl = $sqs->getQueueUrl(['QueueName' => 'foo'])->getQueueUrl();
        $sqs->purgeQueue(['QueueUrl' => $fooQueueUrl])->resolve();
        $sqs->sendMessage(['QueueUrl' => $fooQueueUrl, 'MessageBody' => 'foo'])->resolve();
        $attributes = $sqs->getQueueAttributes(['QueueUrl' => $fooQueueUrl])->getAttributes();
        self::assertEquals(1, (int) $attributes['ApproximateNumberOfMessages']);
        self::assertEquals(0, (int) $attributes['ApproximateNumberOfMessagesNotVisible']);

        $messages = $sqs->receiveMessage(['QueueUrl' => $fooQueueUrl, 'MaxNumberOfMessages' => 1, 'WaitTimeSeconds' => '2'])->getMessages();
        self::assertCount(1, $messages);
        $attributes = $sqs->getQueueAttributes(['QueueUrl' => $fooQueueUrl])->getAttributes();
        self::assertEquals(0, (int) $attributes['ApproximateNumberOfMessages']);
        self::assertEquals(1, (int) $attributes['ApproximateNumberOfMessagesNotVisible']);

        $input = (new DeleteMessageRequest())
            ->setQueueUrl($fooQueueUrl)
            ->setReceiptHandle($messages[0]->getReceiptHandle());

        $sqs->deleteMessage($input)->resolve();

        $attributes = $sqs->getQueueAttributes(['QueueUrl' => $fooQueueUrl])->getAttributes();
        self::assertEquals(0, (int) $attributes['ApproximateNumberOfMessages']);
        self::assertEquals(0, (int) $attributes['ApproximateNumberOfMessagesNotVisible']);
    }

    public function testDeleteQueue()
    {
        $sqs = $this->getClient();

        $sqs->createQueue(['QueueName' => 'foo'])->resolve();
        $fooQueueUrl = $sqs->getQueueUrl(['QueueName' => 'foo'])->getQueueUrl();
        self::assertContains($fooQueueUrl, iterator_to_array($sqs->listQueues()));

        $input = (new DeleteQueueRequest())
            ->setQueueUrl($fooQueueUrl);

        $sqs->deleteQueue($input)->resolve();

        self::assertNotContains($fooQueueUrl, iterator_to_array($sqs->listQueues()));
    }

    public function testGetQueueAttributes()
    {
        $sqs = $this->getClient();

        $sqs->createQueue(['QueueName' => 'foo'])->resolve();
        $fooQueueUrl = $sqs->getQueueUrl(['QueueName' => 'foo'])->getQueueUrl();

        $input = (new GetQueueAttributesRequest())
            ->setQueueUrl($fooQueueUrl);
        $attributes = $sqs->getQueueAttributes($input);

        self::assertArrayHasKey('ApproximateNumberOfMessages', $attributes->getAttributes());
        self::assertArrayHasKey('ApproximateNumberOfMessagesNotVisible', $attributes->getAttributes());
    }

    public function testGetQueueUrl()
    {
        $sqs = $this->getClient();

        $sqs->createQueue(['QueueName' => 'foo'])->resolve();

        $input = (new GetQueueUrlRequest())
            ->setQueueName('foo');
        $queueUrl = $sqs->getQueueUrl($input);

        self::assertStringContainsString('http', $queueUrl->getQueueUrl());
        self::assertStringContainsString('foo', $queueUrl->getQueueUrl());
    }

    public function testListQueues()
    {
        $sqs = $this->getClient();

        $sqs->createQueue(['QueueName' => 'foo'])->resolve();
        $sqs->createQueue(['QueueName' => 'bar'])->resolve();
        $fooQueueUrl = $sqs->getQueueUrl(['QueueName' => 'foo'])->getQueueUrl();
        $barQueueUrl = $sqs->getQueueUrl(['QueueName' => 'bar'])->getQueueUrl();

        $input = new ListQueuesRequest();
        $queues = $sqs->listQueues($input);

        self::assertContains($fooQueueUrl, $queues);
        self::assertContains($barQueueUrl, $queues);

        $input = (new ListQueuesRequest())
            ->setQueueNamePrefix('fo');
        $queues = $sqs->listQueues($input);

        self::assertContains($fooQueueUrl, $queues);
        self::assertNotContains($barQueueUrl, $queues);
    }

    public function testPurgeQueue()
    {
        $sqs = $this->getClient();

        $sqs->createQueue(['QueueName' => 'foo'])->resolve();
        $fooQueueUrl = $sqs->getQueueUrl(['QueueName' => 'foo'])->getQueueUrl();
        $sqs->purgeQueue(['QueueUrl' => $fooQueueUrl])->resolve();
        $sqs->sendMessage(['QueueUrl' => $fooQueueUrl, 'MessageBody' => 'foo'])->resolve();
        self::assertEquals(1, (int) $sqs->getQueueAttributes(['QueueUrl' => $fooQueueUrl])->getAttributes()['ApproximateNumberOfMessages']);

        $input = (new PurgeQueueRequest())
            ->setQueueUrl($fooQueueUrl);
        $sqs->purgeQueue($input)->resolve();

        self::assertEquals(0, (int) $sqs->getQueueAttributes(['QueueUrl' => $fooQueueUrl])->getAttributes()['ApproximateNumberOfMessages']);
    }

    public function testQueueExists()
    {
        $sqs = $this->getClient();

        $sqs->createQueue(['QueueName' => 'foo'])->resolve();
        $waiter = $sqs->queueExists(['QueueName' => 'foo']);
        self::assertTrue($waiter->wait());
        self::assertTrue($waiter->isSuccess());

        $waiter = $sqs->queueExists(['QueueName' => 'does-not-exists']);
        self::assertFalse($waiter->isSuccess());

        // Can't test the above code, because Fake SQS returns the wrong error
        // self::assertFalse($waiter->wait(0.01));
    }

    public function testReceiveMessage()
    {
        $sqs = $this->getClient();

        $sqs->createQueue(['QueueName' => 'foo'])->resolve();
        $fooQueueUrl = $sqs->getQueueUrl(['QueueName' => 'foo'])->getQueueUrl();
        $sqs->purgeQueue(['QueueUrl' => $fooQueueUrl])->resolve();
        $sqs->sendMessage(['QueueUrl' => $fooQueueUrl, 'MessageBody' => 'foo', 'MessageAttributes' => [
            'foo' => ['DataType' => 'String', 'StringValue' => 'bar'],
        ]])->resolve();
        $attributes = $sqs->getQueueAttributes(['QueueUrl' => $fooQueueUrl])->getAttributes();
        self::assertEquals(1, (int) $attributes['ApproximateNumberOfMessages']);
        self::assertEquals(0, (int) $attributes['ApproximateNumberOfMessagesNotVisible']);

        $input = (new ReceiveMessageRequest())
            ->setQueueUrl($fooQueueUrl)
            ->setMaxNumberOfMessages(1)
            ->setWaitTimeSeconds(1)
            ->setMessageAttributeNames(['All'])
        ;

        $messages = $sqs->receiveMessage($input)->getMessages();
        self::assertCount(1, $messages);
        self::assertEquals('foo', $messages[0]->getBody());
        self::assertCount(1, $messages[0]->getMessageAttributes());
        self::assertArrayHasKey('foo', $messages[0]->getMessageAttributes());
        self::assertSame('bar', $messages[0]->getMessageAttributes()['foo']->getStringValue());
    }

    public function testSendMessage()
    {
        $sqs = $this->getClient();

        $result = $sqs->createQueue(['QueueName' => 'bar']);
        $result->resolve();
        self::assertEquals(200, $result->info()['status']);

        $input = new SendMessageRequest();
        $input
            ->setQueueUrl('https://foo.com/bar')
            ->setMessageBody('foobar');

        $result = $sqs->sendMessage($input);
        self::assertNotNull($result->getMessageId());
    }

    private function getClient(): SqsClient
    {
        return new SqsClient([
            'endpoint' => 'http://localhost:9494',
        ], new NullProvider());
    }
}
