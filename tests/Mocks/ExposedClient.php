<?php

namespace MicroShard\JsonRpcClient\Test\Mocks;

use MicroShard\JsonRpcClient\Client;
use GuzzleHttp\Client as GuzzleClient;
use MicroShard\JsonRpcClient\Client\Request;

class ExposedClient extends Client
{

    /**
     * @return GuzzleClient
     */
    public function exposedGetGuzzle(): GuzzleClient
    {
        return $this->getGuzzle();
    }

    /**
     * @param string $resource
     * @param string $method
     * @return string
     */
    public function exposedGetVersionForRequest(string $resource, string $method)
    {
        return $this->getVersionForRequest($resource, $method);
    }

    /**
     * @param Request $request
     * @return Client
     */
    public function exposedSetAuthentication(Request $request): Client
    {
        $this->setAuthentication($request);
        return $this;
    }
}