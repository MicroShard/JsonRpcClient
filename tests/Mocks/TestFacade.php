<?php

namespace MicroShard\JsonRpcClient\Test\Mocks;

use MicroShard\JsonRpcClient\Client\Request;
use MicroShard\JsonRpcClient\Facade;

class TestFacade extends Facade
{
    /**
     * @return Request
     */
    public function getTestRequest()
    {
        $request = new Request();
        $this->prepareRequest($request);
        return $request;
    }
}