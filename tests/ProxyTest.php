<?php

use GuzzleHttp\Psr7\Response;
use MicroShard\JsonRpcClient\Client;
use MicroShard\JsonRpcClient\Test\Mocks\ExposedClient;
use MicroShard\JsonRpcClient\Test\Mocks\TestGuzzleClient;
use MicroShard\JsonRpcClient\Test\Mocks\TestHttpRequest;
use phpmock\MockBuilder;
use PHPUnit\Framework\TestCase;

class ProxyTest extends TestCase
{

    protected $httpResponseCodeMock;
    protected $httpResponseHeaderMock;

    public function setUp()
    {
        // mock http_response_code
        if (is_null($this->httpResponseCodeMock)) {
            $this->httpResponseCodeMock = $this->mockGlobalMethod('http_response_code', function ($value) {
            });
        }
        $this->httpResponseCodeMock->enable();

        // mock header
        if (is_null($this->httpResponseHeaderMock)) {
            $this->httpResponseHeaderMock = $this->mockGlobalMethod('header', function ($value) {
            });
        }
        $this->httpResponseHeaderMock->enable();
    }

    public function tearDown()
    {
        $this->httpResponseCodeMock->disable();
        $this->httpResponseHeaderMock->disable();
    }

    /**
     * @param string $methodName
     * @param Closure $mockFunction
     * @return \phpmock\Mock
     */
    public function mockGlobalMethod(string $methodName, Closure $mockFunction)
    {
        $builder = new MockBuilder();
        $builder->setNamespace('MicroShard\JsonRpcClient')
            ->setName($methodName)
            ->setFunction($mockFunction);
        return $builder->build();
    }

    public function testSuccess()
    {
        $httpResponseContent = '{"status":200,"payload":{"value":"test result"},"resource":"test_resource","method":"test_method","version":"5","message":"OK"}';

        $guzzle = new TestGuzzleClient();
        $guzzle->setNextResponse(new Response(200, [], $httpResponseContent));

        $client = new ExposedClient('some.domain.com', function (){
            return ['token' => '12345'];
        });
        $client->setGuzzleFactory(function() use ($guzzle) {
            return $guzzle;
        });
        $client->setVersions([
            'test_resource' => [
                'test_method' => 5
            ]
        ]);

        $httpRequest = new TestHttpRequest();
        $httpRequest->setBodyData([
            'resource' => 'test_resource',
            'method' => 'test_method',
            'payload' => [
                'some' => 'value'
            ]
        ]);

        $proxy = new \MicroShard\JsonRpcClient\Proxy();
        $proxy->run($client, $httpRequest);

        $this->expectOutputString($httpResponseContent);
        $this->assertEquals("some.domain.com/test_resource/test_method", $guzzle->getLastUrl());

        $options = $guzzle->getLastOptions();
        $this->assertArraySubset([
            'resource' => 'test_resource',
            'method' => 'test_method',
            'payload' => [
                'some' => 'value'
            ],
            'version' => "5",
            'auth' => [
                'token' => '12345'
            ]
        ], $options['json']);
    }

    public function testError()
    {
        $client = new Client('some.domain.com');
        $httpRequest = new TestHttpRequest();
        $httpRequest->setBody("{error");

        $proxy = new \MicroShard\JsonRpcClient\Proxy();
        $proxy->run($client, $httpRequest);

        $this->expectOutputString('{"status":400,"error":100,"message":"malformed request json","payload":[]}');
    }
}