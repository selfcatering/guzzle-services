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
        $json = json_decode('{"HotelListResponse":{"customerSessionId":"init","numberOfRoomsRequested":0,"moreResultsAvailable":false,"HotelList":{"@size":"8","@activePropertyCount":"8","HotelSummary":[{"@order":"0","hotelId":347157,"name":"Hilton Gdansk","address1":"Targ Rybny 1","city":"Gdansk","postalCode":"80-838","countryCode":"PL","airportCode":"GDN","propertyCategory":1,"hotelRating":5,"hotelRatingDisplay":"Star","confidenceRating":52,"amenityMask":1442635,"tripAdvisorRating":4.5,"tripAdvisorReviewCount":1379,"tripAdvisorRatingUrl":"http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/4.5-123456-4.gif","locationDescription":"Near Gdansk Crane","shortDescription":"&lt;p&gt;&lt;b&gt;Property Location&lt;/b&gt; &lt;br /&gt;With a stay at Hilton Gdansk, you&apos;ll be centrally located in Gdansk, minutes from Polish Baltic Philharmonic and Gdansk Crane.  This 5-star hotel is close to Artus","highRate":597.78,"lowRate":90.68,"rateCurrencyCode":"EUR","latitude":54.35334,"longitude":18.65768,"proximityDistance":14.041499,"proximityUnit":"MI","hotelInDestination":false,"thumbNailUrl":"/hotels/4000000/3580000/3576700/3576687/3576687_60_t.jpg","deepLink":"http://www.travelnow.com/templates/501305/hotels/347157/overview?lang=en&amp;currency=EUR&amp;standardCheckin=2017-05-09&amp;standardCheckout=2017-05-23"},{"@order":"2","hotelId":216322,"name":"Hotel Rezydent","address1":"Plac Konstytucji 3 Maja 3","city":"Sopot","postalCode":"81-704","countryCode":"PL","airportCode":"GDN","propertyCategory":1,"hotelRating":5,"hotelRatingDisplay":"Star","confidenceRating":93,"amenityMask":1442633,"tripAdvisorRating":4,"tripAdvisorReviewCount":248,"tripAdvisorRatingUrl":"http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/4.0-123456-4.gif","locationDescription":"Near Sierakowskich Manor","shortDescription":"&lt;p&gt;&lt;b&gt;Property Location&lt;/b&gt; &lt;br /&gt;With a stay at Hotel Rezydent, you&apos;ll be centrally located in Sopot, steps from Sierakowskich Manor and minutes from Crooked House.  This 5-star hotel is within","highRate":285.92,"lowRate":63.51,"rateCurrencyCode":"EUR","latitude":54.44282,"longitude":18.56325,"proximityDistance":6.839875,"proximityUnit":"MI","hotelInDestination":false,"thumbNailUrl":"/hotels/1000000/980000/979400/979398/979398_96_t.jpg","deepLink":"http://www.travelnow.com/templates/501305/hotels/216322/overview?lang=en&amp;currency=EUR&amp;standardCheckin=2017-05-09&amp;standardCheckout=2017-05-23"},{"@order":"7","hotelId":212104,"name":"Sofitel Grand Sopot","address1":"Ul. Powstancow Warszawy 12-14","city":"Sopot","postalCode":"81-718","countryCode":"PL","airportCode":"GDN","propertyCategory":1,"hotelRating":5,"hotelRatingDisplay":"Star","confidenceRating":50,"amenityMask":1463115,"tripAdvisorRating":4.5,"tripAdvisorReviewCount":823,"tripAdvisorRatingUrl":"http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/4.5-123456-4.gif","locationDescription":"In Sopot (Dolny Sopot)","shortDescription":"&lt;p&gt;&lt;b&gt;Property Location&lt;/b&gt; &lt;br /&gt;With a stay at Sofitel Grand Sopot in Sopot (Dolny Sopot), you&apos;ll be minutes from Grand Hotel and Atelier Theatre.  This 5-star hotel is within close proximity of","highRate":188.58,"lowRate":101.71,"rateCurrencyCode":"EUR","latitude":54.44695,"longitude":18.56791,"proximityDistance":6.7637777,"proximityUnit":"MI","hotelInDestination":false,"thumbNailUrl":"/hotels/1000000/930000/920600/920581/8bffe2aa_t.jpg","deepLink":"http://www.travelnow.com/templates/501305/hotels/212104/overview?lang=en&amp;currency=EUR&amp;standardCheckin=2017-05-09&amp;standardCheckout=2017-05-23"}]}}}');

        $body = \GuzzleHttp\json_encode($json);
        $response = new Response(200, ['Content-Type' => 'application/json'], $body);
        $mock = new MockHandler([$response]);

        $httpClient = new Client(['handler' => $mock]);

        $description = new Description([
            'operations' => [
                'foo' => [
                    'uri' => 'http://httpbin.org',
                    'httpMethod' => 'GET',
                    'responseModel' => 'HotelListModel'
                ]
            ],
            'models' => [
                'HotelListModel' => [
                    'type' => 'object',
                    'location' => 'json',
                    'properties' => [
                        'HotelListResponse' => [
                            'type' => 'object',
                            'location' => 'json',
                            'properties' => [
                                'customerSessionId' => [
                                    'type' => 'string',
                                ],
                                'numberOfRoomsRequested' => [
                                    'type' => 'integer',
                                ],
                                'moreResultsAvailable' => [
                                    'type' => 'boolean',
                                ],
                                'HotelList' => [
                                    'type' => 'object',
                                    'location' => 'json',
                                    'items' => [
                                        'type' => 'array',
                                        'properties' => [
                                            '@size' => [
                                                'type' => 'integer',
                                            ],
                                            '@activePropertyCount' => [
                                                'type' => 'integer',
                                            ],
                                            'GetHotelSummary' => [
                                                'location' => 'json',
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    'location' => 'json',
                                                    'properties' => [
                                                        '@order' => [
                                                            'type' => 'integer',
                                                            'location' => 'json',
                                                        ],
                                                        'hotelId' => [
                                                            'type' => 'integer',
                                                        ],
                                                        'name' => [
                                                            'type' => 'string',
                                                        ],
                                                        'address1' => [
                                                            'type' => 'string',
                                                        ],
                                                        'city' => [
                                                            'type' => 'string',
                                                        ],
                                                        'postalCode' => [
                                                            'type' => 'string',
                                                        ],
                                                        'countryCode' => [
                                                            'type' => 'string',
                                                        ],
                                                        'airportCode' => [
                                                            'type' => 'string',
                                                        ],
                                                        'propertyCategory' => [
                                                            'type' => 'integer',
                                                        ],
                                                        'hotelRating' => [
                                                            'type' => 'integer',
                                                        ],
                                                        'hotelRatingDisplay' => [
                                                            'type' => 'string',
                                                        ],
                                                        'confidenceRating' => [
                                                            'type' => 'integer',
                                                        ],
                                                        'amenityMask' => [
                                                            'type' => 'string',
                                                        ],
                                                        'tripAdvisorRating' => [
                                                            'type' => 'numeric',
                                                        ],
                                                        'tripAdvisorReviewCount' => [
                                                            'type' => 'integer',
                                                        ],
                                                        'tripAdvisorRatingUrl' => [
                                                            'type' => 'string',
                                                        ],
                                                        'locationDescription' => [
                                                            'type' => 'string',
                                                        ],
                                                        'shortDescription' => [
                                                            'type' => 'string',
                                                        ],
                                                        'highRate' => [
                                                            'type' => 'boolean',
                                                        ],
                                                        'lowRate' => [
                                                            'type' => 'numeric',
                                                        ],
                                                        'rateCurrencyCode' => [
                                                            'type' => 'string',
                                                        ],
                                                        'latitude' => [
                                                            'type' => 'numeric',
                                                        ],
                                                        'longitude' => [
                                                            'type' => 'numeric',
                                                        ],
                                                        'proximityDistance' => [
                                                            'type' => 'numeric',
                                                        ],
                                                        'proximityUnit' => [
                                                            'type' => 'string',
                                                        ],
                                                        'hotelInDestination' => [
                                                            'type' => 'boolean',
                                                        ],
                                                        'thumbNailUrl' => [
                                                            'type' => 'string',
                                                        ],
                                                        'deepLink' => [
                                                            'type' => 'string',
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
            ],
        ]);

        $guzzle = new GuzzleClient($httpClient, $description);
        /** @var ResultInterface $result */
        $result = $guzzle->foo();

        var_dump($result);
        die();
        $expected = [
            'HotelListResponse' => [
                'customerSessionId' => 'init',
                'numberOfRoomsRequested' => 0,
                'moreResultsAvailable' => false,
                'HotelList' => [
                    '@size' => 8,
                    '@activePropertyCount' => 8,
                    'HotelSummary' => [
                        [
                            '@order' => '0',
                            'hotelId' => 347157,
                            'name' => 'Hilton Gdansk',
                            'address1' => 'Targ Rybny 1',
                            'city' => 'Gdansk',
                            'postalCode' => '80-838',
                            'countryCode' => 'PL',
                            'airportCode' => 'GDN',
                            'propertyCategory' => 1,
                            'hotelRating' => 5,
                            'hotelRatingDisplay' => 'Star',
                            'confidenceRating' => 52,
                            'amenityMask' => 1442635,
                            'tripAdvisorRating' => 4.5,
                            'tripAdvisorReviewCount' => 1379,
                            'tripAdvisorRatingUrl' => 'http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/4.5-123456-4.gif',
                            'locationDescription' => 'Near Gdansk Crane',
                            'shortDescription' => '&lt;p&gt;&lt;b&gt;Property Location&lt;/b&gt; &lt;br /&gt;With a stay at Hilton Gdansk, you&apos;ll be centrally located in Gdansk, minutes from Polish Baltic Philharmonic and Gdansk Crane.  This 5-star hotel is close to Artus',
                            'highRate' => 597.77999999999997,
                            'lowRate' => 90.680000000000007,
                            'rateCurrencyCode' => 'EUR',
                            'latitude' => 54.353340000000003,
                            'longitude' => 18.657679999999999,
                            'proximityDistance' => 14.041499,
                            'proximityUnit' => 'MI',
                            'hotelInDestination' => false,
                            'thumbNailUrl' => '/hotels/4000000/3580000/3576700/3576687/3576687_60_t.jpg',
                            //'deepLink' => 'http://www.travelnow.com/templates/501305/hotels/347157/overview?lang=en&amp;currency=EUR&amp;standardCheckin=2017-05-09&amp;standardCheckout=2017-05-23',
                        ],
                        [
                            '@order' => '2',
                            'hotelId' => 216322,
                            'name' => 'Hotel Rezydent',
                            'address1' => 'Plac Konstytucji 3 Maja 3',
                            'city' => 'Sopot',
                            'postalCode' => '81-704',
                            'countryCode' => 'PL',
                            'airportCode' => 'GDN',
                            'propertyCategory' => 1,
                            'hotelRating' => 5,
                            'hotelRatingDisplay' => 'Star',
                            'confidenceRating' => 93,
                            'amenityMask' => 1442633,
                            'tripAdvisorRating' => 4,
                            'tripAdvisorReviewCount' => 248,
                            'tripAdvisorRatingUrl' => 'http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/4.0-123456-4.gif',
                            'locationDescription' => 'Near Sierakowskich Manor',
                            'shortDescription' => '&lt;p&gt;&lt;b&gt;Property Location&lt;/b&gt; &lt;br /&gt;With a stay at Hotel Rezydent, you&apos;ll be centrally located in Sopot, steps from Sierakowskich Manor and minutes from Crooked House.  This 5-star hotel is within',
                            'highRate' => 285.92000000000002,
                            'lowRate' => 63.509999999999998,
                            'rateCurrencyCode' => 'EUR',
                            'latitude' => 54.442819999999998,
                            'longitude' => 18.56325,
                            'proximityDistance' => 6.8398750000000001,
                            'proximityUnit' => 'MI',
                            'hotelInDestination' => false,
                            'thumbNailUrl' => '/hotels/1000000/980000/979400/979398/979398_96_t.jpg',
                            //'deepLink' => 'http://www.travelnow.com/templates/501305/hotels/216322/overview?lang=en&amp;currency=EUR&amp;standardCheckin=2017-05-09&amp;standardCheckout=2017-05-23',
                        ],
                        [
                            '@order' => '7',
                            'hotelId' => 212104,
                            'name' => 'Sofitel Grand Sopot',
                            'address1' => 'Ul. Powstancow Warszawy 12-14',
                            'city' => 'Sopot',
                            'postalCode' => '81-718',
                            'countryCode' => 'PL',
                            'airportCode' => 'GDN',
                            'propertyCategory' => 1,
                            'hotelRating' => 5,
                            'hotelRatingDisplay' => 'Star',
                            'confidenceRating' => 50,
                            'amenityMask' => 1463115,
                            'tripAdvisorRating' => 4.5,
                            'tripAdvisorReviewCount' => 823,
                            'tripAdvisorRatingUrl' => 'http://www.tripadvisor.com/img/cdsi/img2/ratings/traveler/4.5-123456-4.gif',
                            'locationDescription' => 'In Sopot (Dolny Sopot)',
                            'shortDescription' => '&lt;p&gt;&lt;b&gt;Property Location&lt;/b&gt; &lt;br /&gt;With a stay at Sofitel Grand Sopot in Sopot (Dolny Sopot), you&apos;ll be minutes from Grand Hotel and Atelier Theatre.  This 5-star hotel is within close proximity of',
                            'highRate' => 188.58000000000001,
                            'lowRate' => 101.70999999999999,
                            'rateCurrencyCode' => 'EUR',
                            'latitude' => 54.446950000000001,
                            'longitude' => 18.567910000000001,
                            'proximityDistance' => 6.7637777000000003,
                            'proximityUnit' => 'MI',
                            'hotelInDestination' => false,
                            'thumbNailUrl' => '/hotels/1000000/930000/920600/920581/8bffe2aa_t.jpg',
                            //'deepLink' => 'http://www.travelnow.com/templates/501305/hotels/212104/overview?lang=en&amp;currency=EUR&amp;standardCheckin=2017-05-09&amp;standardCheckout=2017-05-23',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
