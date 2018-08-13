<?php

use PHPUnit\Framework\TestCase;

class FacadeTest extends TestCase
{

    public function testGeneral()
    {
        $client = new \MicroShard\JsonRpcClient\Client('some.url');
        $resource = 'test_resource';

        $facade = new \MicroShard\JsonRpcClient\Test\Mocks\TestFacade($client, $resource);
        $request = $facade->getTestRequest();

        $this->assertEquals($resource, $request->getResource());
        $this->assertEquals($client, $request->getClient());
    }

}