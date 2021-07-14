<?php

namespace AsyncAws\CloudWatch\Tests\Unit\Input;

use AsyncAws\CloudWatch\Input\GetMetricDataInput;
use AsyncAws\CloudWatch\ValueObject\Dimension;
use AsyncAws\CloudWatch\ValueObject\LabelOptions;
use AsyncAws\CloudWatch\ValueObject\Metric;
use AsyncAws\CloudWatch\ValueObject\MetricDataQuery;
use AsyncAws\CloudWatch\ValueObject\MetricStat;
use AsyncAws\Core\Test\TestCase;

class GetMetricDataInputTest extends TestCase
{
    public function testRequest(): void
    {
        self::fail('Not implemented');

        $input = new GetMetricDataInput([
            'MetricDataQueries' => [new MetricDataQuery([
                'Id' => 'change me',
                'MetricStat' => new MetricStat([
                    'Metric' => new Metric([
                        'Namespace' => 'change me',
                        'MetricName' => 'change me',
                        'Dimensions' => [new Dimension([
                            'Name' => 'change me',
                            'Value' => 'change me',
                        ])],
                    ]),
                    'Period' => 1337,
                    'Stat' => 'change me',
                    'Unit' => 'change me',
                ]),
                'Expression' => 'change me',
                'Label' => 'change me',
                'ReturnData' => false,
                'Period' => 1337,
            ])],
            'StartTime' => new \DateTimeImmutable(),
            'EndTime' => new \DateTimeImmutable(),
            'NextToken' => 'change me',
            'ScanBy' => 'change me',
            'MaxDatapoints' => 1337,
            'LabelOptions' => new LabelOptions([
                'Timezone' => 'change me',
            ]),
        ]);

        // see https://docs.aws.amazon.com/AmazonCloudWatch/latest/APIReference/API_GetMetricData.html
        $expected = '
            POST / HTTP/1.0
            Content-Type: application/x-www-form-urlencoded

            Action=GetMetricData
            &Version=2010-08-01
                ';

        self::assertRequestEqualsHttpRequest($expected, $input->request());
    }
}
