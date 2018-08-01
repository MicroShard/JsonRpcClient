<?php

namespace MicroShard\JsonRpcClient\Test\Mocks;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class TestGuzzleClient extends Client
{

    /**
     * @var Response
     */
    protected $nextResponse;

    /**
     * @var string
     */
    protected $lastUrl;

    /**
     * @var array
     */
    protected $lastOptions;

    /**
     * @param \Psr\Http\Message\UriInterface|string $url
     * @param array $options
     * @return Response|\Psr\Http\Message\ResponseInterface
     */
    public function post($url, array $options)
    {
        $this->lastUrl = $url;
        $this->lastOptions = $options;

        return $this->nextResponse;
    }

    /**
     * @return string
     */
    public function getLastUrl(): string
    {
        return $this->lastUrl;
    }

    /**
     * @return array
     */
    public function getLastOptions(): array
    {
        return $this->lastOptions;
    }

    /**
     * @param Response $nextResponse
     * @return TestGuzzleClient
     */
    public function setNextResponse(Response $nextResponse): TestGuzzleClient
    {
        $this->nextResponse = $nextResponse;
        return $this;
    }
}