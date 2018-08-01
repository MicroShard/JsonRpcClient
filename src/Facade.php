<?php

namespace MicroShard\JsonRpcClient;

use MicroShard\JsonRpcClient\Client\Request;

class Facade
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @param Client $client
     * @param $resource
     */
    public function __construct(Client $client, string $resource)
    {
        $this->client = $client;
        $this->resource = $resource;
    }

    /**
     * @return Client
     */
    protected function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return string
     */
    protected function getResource(): string
    {
        return $this->resource;
    }

    /**
     * @param Request $request
     * @param string $version
     * @return Request
     */
    protected function prepareRequest(Request $request, string $version = null): Request
    {
        $request->setClient($this->getClient());
        $request->setResource($this->getResource());
        $request->setVersion($version);
        return $request;
    }
}
