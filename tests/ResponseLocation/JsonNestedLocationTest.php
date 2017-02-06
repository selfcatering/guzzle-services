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
class JsonLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group ResponseLocation
     */
    public function testVisitsNestedArrayOfObjects()
    {
        $json = json_decode('{"scalar":"foo","nester":{"nested":[{"foo":111,"bar":123,"baz":false},{"foo":222,"bar":345,"baz":true},{"foo":333,"bar":678,"baz":true}]}}');

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
                        'scalar' => [
                            'type' => 'string'
                        ],
                        'nester' => [
                            'type' => 'object',
                            'location' => 'json',
                            'nested' => [
                                'type' => 'object',
                                'location' => 'json',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'bar' => [
                                            'type' => 'string',
                                        ],
                                        'baz' => [
                                            'type' => 'string',
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $guzzle = new GuzzleClient($httpClient, $description);
        /** @var ResultInterface $result */
        $result = $guzzle->foo();
        $expected = [
            'scalar' => 'foo',
            'nester' => [
                'nested' => [
                    [
                        'bar' => 123,
                        'baz' => false,
                    ],
                    [
                        'bar' => 345,
                        'baz' => true,
                    ],
                    [
                        'bar' => 678,
                        'baz' => true,
                    ],
                ]
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }
}
