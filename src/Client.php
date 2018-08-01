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
     * @param string $baseUrl
     * @param string $authMode
     * @param array|null $authData
     */
    public function __construct(string $baseUrl, string $authMode, array $authData = null)
    {
        $this->baseUrl = $baseUrl;
        $this->authMode = $authMode;
        $this->authData = $authData;
        $this->guzzleFactory = function (){
            return new GuzzleClient();
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
     * @param Request $request
     * @return $this
     */
    protected function setAuthentication(Request $request): Client
    {
        switch ($this->authMode){
            case self::AUTH_MODE_STATIC_TOKEN:
                $request->setAuthentication([
                    'token' => $this->authData['token']
                ]);
                break;
            case self::AUTH_MODE_NONE:
            default:
                //nothing
        }
        return $this;
    }

    /**
     * @param string $resource
     * @param string $method
     * @return string
     */
    protected function getVersionForRequest(string $resource, string $method): ?string
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

        if ($response->getStatusCode() != 200) {
            $responseData = [
                'error' => $response->getStatusCode(),
                'message' => 'invalid response'
            ];
        } else {
            $responseData = json_decode($response->getBody(), true);
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
