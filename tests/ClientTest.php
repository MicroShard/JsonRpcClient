<?php

namespace MicroShard\JsonRpcClient\Test;

use MicroShard\JsonRpcClient\Client\Request;
use MicroShard\JsonRpcClient\Test\Mocks\ExposedClient;
use GuzzleHttp\Client as GuzzleClient;
use MicroShard\JsonRpcClient\Test\Mocks\TestGuzzleClient;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{

    public function testDefaultGuzzleFactory()
    {
        $client = new ExposedClient('some.domain.com', ExposedClient::AUTH_MODE_NONE);
        $guzzle = $client->exposedGetGuzzle();

        $this->assertInstanceOf(GuzzleClient::class, $guzzle);
        $this->assertNotInstanceOf(TestGuzzleClient::class, $guzzle);
    }

    public function testCustomGuzzleFactory()
    {
        $client = new ExposedClient('some.domain.com', ExposedClient::AUTH_MODE_NONE);
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
        $client = new ExposedClient('some.domain.com', ExposedClient::AUTH_MODE_NONE);
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
        $client = new ExposedClient('some.domain.com', ExposedClient::AUTH_MODE_STATIC_TOKEN, ['token' => '12345']);
        $request = new Request();

        $client->exposedSetAuthentication($request);
        $data = $request->getData();

        $this->assertArrayHasKey('auth', $data);
        $this->assertArraySubset(['token' => '12345'], $data['auth']);
    }
}
