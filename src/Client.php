<?php

namespace MicroShard\JsonRpcClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use MicroShard\JsonRpcClient\Client\Request;
use MicroShard\JsonRpcClient\Client\Response;

class Client
{

    const AUTH_MODE_STATIC_TOKEN = 'static_token';
    const AUTH_MODE_NONE = 'none';

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $authMode;

    /**
     * @var array
     */
    private $authData;

    /**
     * @var array
     */
    private $versions = [];

    /**
     * @var GuzzleClient
     */
    private $guzzle;

    /**
     * @var \Closure
     */
    private $guzzleFactory;

    /**
     * @var \Closure
     */
    private $requestIdFactory;

    /**
     * @var string
     */
    private $lastRequestId;

    /**
     * @var \Closure
     */
    private $authFactory;

    /**
     * @param string $baseUrl
     * @param \Closure|null $authFactory
     */
    public function __construct(string $baseUrl, \Closure $authFactory = null)
    {
        $this->baseUrl = $baseUrl;
        $this->authFactory = $authFactory;
        $this->guzzleFactory = function (){
            return new GuzzleClient();
        };
        $this->requestIdFactory = function(){
            return time() . '-' . $this->lastRequestId++;
        };
    }

    /**
     * @param \Closure $factory
     * @return Client
     */
    public function setGuzzleFactory(\Closure $factory): Client
    {
        $this->guzzleFactory = $factory;
        return $this;
    }

    /**
     * @param \Closure $factory
     * @return Client
     */
    public function setRequestIdFactory(\Closure $factory): Client
    {
        $this->requestIdFactory = $factory;
        return $this;
    }

    /**
     * @param Request $request
     * @return $this
     */
    protected function setAuthentication(Request $request): Client
    {
        if ($this->authFactory) {
            $request->setAuthentication(($this->authFactory)());
        }
        return $this;
    }

    protected function setRequestId(Request $request): Client
    {
        if ($this->requestIdFactory) {
            $request->setRequestId(($this->requestIdFactory)());
        }
        return $this;
    }

    /**
     * @param string $resource
     * @param string $method
     * @return string
     */
    protected function getVersionForRequest(string $resource, string $method)
    {
        if (isset($this->versions[$resource]) && isset($this->versions[$resource][$method])) {
            return $this->versions[$resource][$method];
        }
        return null;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function send(Request $request): Response
    {
        $this->setAuthentication($request);
        $this->setRequestId($request);
        if (!$request->getVersion()) {
            if ($version = $this->getVersionForRequest($request->getResource(), $request->getMethod())) {
                $request->setVersion($version);
            }
        }

        return $this->sendRequest($request);
    }

    /**
     * @return GuzzleClient
     */
    protected function getGuzzle(): GuzzleClient
    {
        if (!$this->guzzle) {
            $this->guzzle = ($this->guzzleFactory)();
        }
        return $this->guzzle;
    }

    /**
     * @param Request $request
     * @return Response
     */
    protected function sendRequest(Request $request): Response
    {
        $url = rtrim($this->baseUrl, '/') . '/' . $request->getResource() . '/' . $request->getMethod();
        $response = $this->getGuzzle()->post($url, [RequestOptions::JSON => $request->getData()]);

        $responseData = ($response->getBody() && $response->getStatusCode() == 200) ? json_decode($response->getBody(), true) : null;
        if (!$responseData) {
            $responseData = [
                'error' => $response->getStatusCode(),
                'message' => 'invalid response'
            ];
        }

        return $request->createResponse($responseData);
    }

    /**
     * @return array
     */
    public function getVersions(): array
    {
        return $this->versions;
    }

    /**
     * @param array $versions
     * @return Client
     */
    public function setVersions(array $versions): Client
    {
        $this->versions = $versions;
        return $this;
    }
}
