<?php

namespace MicroShard\JsonRpcClient\Test;

use GuzzleHttp\Psr7\Response;
use MicroShard\JsonRpcClient\Client\Request;
use MicroShard\JsonRpcClient\Test\Mocks\ExposedClient;
use GuzzleHttp\Client as GuzzleClient;
use MicroShard\JsonRpcClient\Test\Mocks\TestGuzzleClient;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{

    public function testDefaultGuzzleFactory()
    {
        $client = new ExposedClient('some.domain.com');
        $guzzle = $client->exposedGetGuzzle();

        $this->assertInstanceOf(GuzzleClient::class, $guzzle);
        $this->assertNotInstanceOf(TestGuzzleClient::class, $guzzle);
    }

    public function testCustomGuzzleFactory()
    {
        $client = new ExposedClient('some.domain.com');
        $client->setGuzzleFactory(function(){
            return new TestGuzzleClient();
        });
        $guzzle = $client->exposedGetGuzzle();

        $this->assertInstanceOf(TestGuzzleClient::class, $guzzle);
    }

    public function testVersions()
    {
        $versions = [
            'resource_a' => [
                'method_a' => 1,
                'method_b' => 2
            ],
            'resource_b' => []
        ];
        $client = new ExposedClient('some.domain.com');
        $client->setVersions($versions);

        $this->assertEquals($versions, $client->getVersions());
        $this->assertNull($client->exposedGetVersionForRequest('resource_b', 'method_a'));
        $this->assertNull($client->exposedGetVersionForRequest('resource_c', 'method_a'));
        $this->assertNull($client->exposedGetVersionForRequest('resource_a', 'method_c'));
        $this->assertEquals(1, $client->exposedGetVersionForRequest('resource_a', 'method_a'));
        $this->assertEquals(2, $client->exposedGetVersionForRequest('resource_a', 'method_b'));
    }

    public function testAuthentication()
    {
        $client = new ExposedClient('some.domain.com', function (){
            return ['token' => '12345'];
        });
        $request = new Request();
        $client->exposedSetAuthentication($request);
        $data = $request->getData();
        $this->assertArrayHasKey('auth', $data);
        $this->assertArraySubset(['token' => '12345'], $data['auth']);

        $client = new ExposedClient('some.domain.com');
        $request = new Request();
        $client->exposedSetAuthentication($request);
        $data = $request->getData();
        $this->assertArrayNotHasKey('auth', $data);
    }

    public function testSendSuccess()
    {
        $rawResponseData = [
            'resource' => 'response_resource',
            'method' => 'response_method',
            'version' => 3,
            'status' => 200,
            'message' => 'OK',
            'payload' => ['some' => 'response_data']
        ];

        $guzzle = new TestGuzzleClient();
        $guzzle->setNextResponse(new Response(200, [], json_encode($rawResponseData)));

        $client = new ExposedClient('some.domain.com', function (){
            return ['token' => '12345'];
        });
        $client->setGuzzleFactory(function() use ($guzzle) {
            return $guzzle;
        });

        $request = new Request();
        $request->setResource('test_resource')
            ->setMethod('test_method')
            ->setVersion(2)
            ->setPayload(['some' => 'data']);

        $response = $client->send($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('response_resource', $response->getResource());
        $this->assertEquals('response_method', $response->getMethod());
        $this->assertEquals(3, $response->getVersion());
        $this->assertEquals(['some' => 'response_data'], $response->getPayload());
        $this->assertEquals($rawResponseData, $response->getRawData());
        $this->assertNull($response->getErrorCode());
        $this->assertEquals('OK', $response->getMessage());
        $this->assertTrue($response->isSuccessful());

        $options = $guzzle->getLastOptions();
        $this->assertEquals('some.domain.com/test_resource/test_method', $guzzle->getLastUrl());
        $this->assertArrayHasKey('json', $options);
        $this->assertArraySubset([
            'resource' => 'test_resource',
            'method' => 'test_method',
            'version' => 2,
            'auth' => ['token' => '12345'],
            'payload' => ['some' => 'data']
        ], $options['json']);
    }

    public function testSendSuccess2()
    {
        $rawResponseData = [
            'resource' => 'response_resource',
            'method' => 'response_method',
            'status' => 400,
            'error' => 777,
            'version' => 5,
            'message' => 'some error message',
            'payload' => ['some' => 'response_data']
        ];

        $guzzle = new TestGuzzleClient();
        $guzzle->setNextResponse(new Response(200, [], json_encode($rawResponseData)));

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

        $request = new Request();
        $request->setResource('test_resource')
            ->setMethod('test_method')
            ->setPayload(['some' => 'data']);

        $request->setClient($client);
        $response = $request->send();


        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('response_resource', $response->getResource());
        $this->assertEquals('response_method', $response->getMethod());
        $this->assertEquals(5, $response->getVersion());
        $this->assertEquals(['some' => 'response_data'], $response->getPayload());
        $this->assertEquals($rawResponseData, $response->getRawData());
        $this->assertEquals(777, $response->getErrorCode());
        $this->assertEquals('some error message', $response->getMessage());
        $this->assertFalse($response->isSuccessful());

        $options = $guzzle->getLastOptions();
        $this->assertEquals('some.domain.com/test_resource/test_method', $guzzle->getLastUrl());
        $this->assertArrayHasKey('json', $options);
        $this->assertArraySubset([
            'resource' => 'test_resource',
            'method' => 'test_method',
            'version' => 5,
            'auth' => ['token' => '12345'],
            'payload' => ['some' => 'data']
        ], $options['json']);
    }

    public function testSendError()
    {
        $guzzle = new TestGuzzleClient();
        $guzzle->setNextResponse(new Response(200, [], "{invalid"));

        $client = new ExposedClient('some.domain.com');
        $client->setGuzzleFactory(function() use ($guzzle) {
            return $guzzle;
        });

        $request = new Request();
        $request->setResource('test_resource')
            ->setMethod('test_method')
            ->setVersion(1)
            ->setPayload(['some' => 'data']);

        $request->setClient($client);
        $response = $request->send();

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals(200, $response->getErrorCode());
        $this->assertEquals('invalid response', $response->getMessage());
        $this->assertFalse($response->isSuccessful());
    }
}
