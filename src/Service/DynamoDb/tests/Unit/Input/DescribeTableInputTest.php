<?php

namespace AsyncAws\DynamoDb\Tests\Unit\Input;

use AsyncAws\Core\Test\TestCase;
use AsyncAws\DynamoDb\Input\DescribeTableInput;

class DescribeTableInputTest extends TestCase
{
    public function testRequest(): void
    {
        $input = new DescribeTableInput([
            'TableName' => 'Music',
        ]);

        // see example-1.json from SDK
        $expected = '
POST / HTTP/1.0
Content-Type: application/x-amz-json-1.0
X-AMZ-Target: DynamoDB_20120810.DescribeTable

{
    "TableName": "Music"
}
';

        self::assertRequestEqualsHttpRequest($expected, $input->request());
    }
}
