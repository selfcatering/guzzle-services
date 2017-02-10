<?php
namespace GuzzleHttp\Tests\Command\Guzzle\ResponseLocation;

use GuzzleHttp\Client;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Command\Guzzle\ResponseLocation\JsonLocation;
use GuzzleHttp\Command\Result;
use GuzzleHttp\Command\ResultInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

/**
 * @covers \GuzzleHttp\Command\Guzzle\ResponseLocation\JsonLocation
 * @covers \GuzzleHttp\Command\Guzzle\Deserializer
 */
class JsonNestedLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group ResponseLocation
     */
    public function testVisitsNestedArrayOfObjects()
    {
        $json = json_decode('{"Hotels":[{"HotelFees":{"@size":"1","HotelFee":{"@description":"MandatoryTax","@amount":"12.33"}}},{"HotelFees":{"@size":"2","HotelFee":[{"@description":"MandatoryTax","@amount":"13.96"},{"@description":"ResortFee","@amount":"14.09"}]}}]}');

        /*
        [
            'Hotels' => [
                0 => [
                    'HotelFees' => [
                        '@size' => '1',
                        'HotelFee' => [
                            '@description' => 'MandatoryTax',
                            '@amount' => '12.33',
                        ],
                    ],
                ],
                1 => [
                    'HotelFees' => [
                        '@size' => '2',
                        'HotelFee' => [
                            0 => [
                                '@description' => 'MandatoryTax',
                                '@amount' => '13.96',
                            ],
                            1 => [
                                '@description' => 'ResortFee',
                                '@amount' => '14.09',
                            ],
                        ],
                    ],
                ],
            ],
        ]
         */

        $body = \GuzzleHttp\json_encode($json);
        $response = new Response(200, ['Content-Type' => 'application/json'], $body);
        $mock = new MockHandler([$response]);

        $httpClient = new Client(['handler' => $mock]);

        $description = new Description([
            'operations' => [
                'foo' => [
                    'uri' => 'http://httpbin.org',
                    'httpMethod' => 'GET',
                    'responseModel' => 'j'
                ]
            ],
            'models' => [
                'j' => [
                    'type' => 'object',
                    'location' => 'json',
                    'properties' => [
                        'Hotels' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'HotelFees' => [
                                        'type' => 'object',
                                        'properties' => [
                                            '@size' => [
                                                'type' => 'integer',
                                            ],
                                            'HotelFee' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    '@description' => [
                                                        'type' => 'string',
                                                    ],
                                                    '@amount' => [
                                                        'type' => 'numeric',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $guzzle = new GuzzleClient($httpClient, $description);
        /** @var ResultInterface $result */
        $result = $guzzle->foo();

        var_dump($result->toArray());
        die();

        $expected = [
            'Hotels' => [
                [
                    'HotelFees' => [
                        '@size' => '1',
                        'HotelFee' => [
                            [
                                '@description' => 'MandatoryTax',
                                '@amount' => '12.33',
                            ],
                        ],
                    ],
                ],
                [
                    'HotelFees' => [
                        '@size' => '2',
                        'HotelFee' => [
                            [
                                '@description' => 'MandatoryTax',
                                '@amount' => '13.96',
                            ],
                            [
                                '@description' => 'ResortFee',
                                '@amount' => '14.09',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $result->toArray());
    }
}
