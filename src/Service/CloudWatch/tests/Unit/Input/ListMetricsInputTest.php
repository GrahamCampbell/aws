<?php

namespace AsyncAws\CloudWatch\Tests\Unit\Input;

use AsyncAws\CloudWatch\Input\ListMetricsInput;
use AsyncAws\CloudWatch\ValueObject\DimensionFilter;
use AsyncAws\Core\Test\TestCase;

class ListMetricsInputTest extends TestCase
{
    public function testRequest(): void
    {
        self::fail('Not implemented');

        $input = new ListMetricsInput([
            'Namespace' => 'change me',
            'MetricName' => 'change me',
            'Dimensions' => [new DimensionFilter([
                'Name' => 'change me',
                'Value' => 'change me',
            ])],
            'NextToken' => 'change me',
            'RecentlyActive' => 'change me',
        ]);

        // see https://docs.aws.amazon.com/AmazonCloudWatch/latest/APIReference/API_ListMetrics.html
        $expected = '
            POST / HTTP/1.0
            Content-Type: application/x-www-form-urlencoded

            Action=ListMetrics
            &Version=2010-08-01
                ';

        self::assertRequestEqualsHttpRequest($expected, $input->request());
    }
}
