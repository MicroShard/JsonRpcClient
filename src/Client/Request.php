<?php

namespace MicroShard\JsonRpcClient\Client;

use MicroShard\JsonRpcClient\Client;
use MicroShard\JsonRpcClient\Exception\RpcClientException;

class Request
{

    /**
     * @var string
     */
    protected $resource;
    /**
     * @var string
     */
    protected $method;
    /**
     * @var int
     */
    protected $version;

    /**
     * @var array
     */
    protected $auth;

    /**
     * @var array
     */
    protected $payload = [];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @return Request
     */
    public function setClient(Client $client): Request
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @param string $resource
     * @return Request
     */
    public function setResource(string $resource): Request
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @param string $method
     * @return Request
     */
    public function setMethod(string $method): Request
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param array $data
     * @return Request
     */
    public function setAuthentication(array $data): Request
    {
        $this->auth = $data;
        return $this;
    }

    /**
     * @param string $version
     * @return Request
     */
    public function setVersion(string $version = null): Request
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @param array $data
     * @return Request
     */
    public function setPayload(array $data): Request
    {
        $this->payload = $data;
        return $this;
    }

    /**
     * @return Request
     */
    protected function beforeData(): Request
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $this->beforeData();

        $data = [
            'resource' => $this->resource,
            'method' => $this->method
        ];
        if ($this->version) {
            $data['version'] = $this->version;
        }
        if ($this->auth) {
            $data['auth'] = $this->auth;
        }
        $data['payload'] = $this->payload;

        return $data;
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param array $data
     * @return Response
     */
    public function createResponse(array $data): Response
    {
        return new Response($data);
    }

    /**
     * @return Response
     * @throws RpcClientException
     */
    public function send(): Response
    {
        if (!$this->client){
            throw new RpcClientException('unable to send request - no client set');
        }
        return $this->getClient()->send($this);
    }
}
