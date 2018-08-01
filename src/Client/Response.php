<?php

namespace MicroShard\JsonRpcClient\Client;

class Response
{

    /**
     * @var int
     */
    protected $statusCode = 500;

    /**
     * @var int
     */
    protected $errorCode;

    /**
     * @var array
     */
    protected $payload = [];

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var array
     */
    protected $rawData = [];

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->rawData = $data;

        if (isset($data['status'])) {
            $this->statusCode = $data['status'];
        }
        if (isset($data['message'])) {
            $this->message = $data['message'];
        }

        if (isset($data['resource'])) {
            $this->resource = $data['resource'];
        }
        if (isset($data['method'])) {
            $this->method = $data['method'];
        }
        if (isset($data['version'])) {
            $this->version = $data['version'];
        }

        if (isset($data['payload']) && is_array($data['payload'])) {
            $this->payload = $data['payload'];
        }

        if (isset($data['error'])) {
            $this->errorCode = $data['error'];
        }
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->getStatusCode() == 200 && $this->getMessage() == 'OK';
    }

    /**
     * @return array
     */
    public function getRawData(): array
    {
        return $this->rawData;
    }
}
