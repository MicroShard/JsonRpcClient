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
     * @param Request $request
     * @param string $resource
     * @param string|null $version
     * @return Request
     */
    public function exposedPrepareRequest(Request $request, string $resource, string $version = null): Request
    {
        return $this->prepareRequest($request, $resource, $version);
    }

    /**
     * @param string $resource
     * @param string $method
     * @return string
     */
    public function exposedGetVersionForRequest(string $resource, string $method): ?string
    {
        return $this->getVersionForRequest($resource, $method);
    }
}