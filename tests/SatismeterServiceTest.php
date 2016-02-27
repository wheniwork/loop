<?php

namespace FeedbackTests;

use Wheniwork\Feedback\Service\SatismeterService;

class SatismeterServiceTest extends FeedbackTestBase
{
    /**
     * @var SatismeterService
     */
    private $service;

    public function setUp()
    {
        $this->service = new SatismeterService(
            $this->getMockHttpClient(),
            'test_key',
            'test_product_id'
        );
    }

    public function testGetSatismeterRequest()
    {
        $request = $this->service->getSatismeterRequest(0);

        $this->assertSame('GET', $request->getMethod());
        $this->assertContains('https://app.satismeter.com/api/responses', (string)$request->getUri());
        $this->assertArrayHasKey('AuthKey', $request->getHeaders());
    }

    public function testGetResponses()
    {
        $responses = $this->service->getResponses(0);

        $this->assertInternalType('array', $responses);
        $this->assertSame(2, count($responses));

        // This is sufficient to test that the JSON parsing is
        // working correctly.
        foreach ($responses as $response) {
            $this->assertObjectHasAttribute('rating', $response);
            $this->assertInternalType('int', $response->rating);

            $this->assertObjectHasAttribute('user', $response);
        }
    }

    public function getMockHttpClient()
    {
        $json = file_get_contents(__DIR__ . '/_fixture/response_satismeter.json');

        $mockHttpResponse = $this
            ->getMockBuilder('\Psr\Http\Message\ResponseInterface')
            ->getMock();
        $mockHttpResponse
            ->method('getBody')
            ->willReturn($json);

        $mockHttpClient = $this
            ->getMockBuilder('GuzzleHttp\Client')
            ->getMock();
        $mockHttpClient
            ->method('send')
            ->willReturn($mockHttpResponse);

        return $mockHttpClient;
    }
}
