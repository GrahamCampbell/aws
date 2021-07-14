<?php

namespace AsyncAws\CloudWatch\Tests\Unit\Input;

use AsyncAws\CloudWatch\Input\GetMetricStatisticsInput;
use AsyncAws\CloudWatch\ValueObject\Dimension;
use AsyncAws\Core\Test\TestCase;

class GetMetricStatisticsInputTest extends TestCase
{
    public function testRequest(): void
    {
        self::fail('Not implemented');

        $input = new GetMetricStatisticsInput([
            'Namespace' => 'change me',
            'MetricName' => 'change me',
            'Dimensions' => [new Dimension([
                'Name' => 'change me',
                'Value' => 'change me',
            ])],
            'StartTime' => new \DateTimeImmutable(),
            'EndTime' => new \DateTimeImmutable(),
            'Period' => 1337,
            'Statistics' => ['change me'],
            'ExtendedStatistics' => ['change me'],
            'Unit' => 'change me',
        ]);

        // see https://docs.aws.amazon.com/AmazonCloudWatch/latest/APIReference/API_GetMetricStatistics.html
        $expected = '
            POST / HTTP/1.0
            Content-Type: application/x-www-form-urlencoded

            Action=GetMetricStatistics
            &Version=2010-08-01
                ';

        self::assertRequestEqualsHttpRequest($expected, $input->request());
    }
}
