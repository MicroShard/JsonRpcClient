<?php

namespace MicroShard\JsonRpcClient\Proxy;

use MicroShard\JsonRpcClient\Client\Request;

class ProxyRequest extends Request
{
    /**
     * @var array
     */
    protected $proxyData = [];

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data): ProxyRequest
    {
        $this->proxyData = $data;
        $this->setResource($data['resource']);
        $this->setMethod($data['method']);
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if ($this->version) {
            $this->proxyData['version'] = $this->version;
        }
        if ($this->auth) {
            $this->proxyData['auth'] = $this->auth;
        }
        return $this->proxyData;
    }
}