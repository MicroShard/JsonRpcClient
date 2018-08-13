<?php

namespace MicroShard\JsonRpcClient;

use MicroShard\JsonRpcClient\Client\Response;
use MicroShard\JsonRpcClient\Proxy\ProxyRequest;
use Psr\Http\Message\ServerRequestInterface;

class Proxy
{
    /**
     * @param Client $client
     * @param ServerRequestInterface $request
     */
    public function run(Client $client, ServerRequestInterface $request): void
    {
        $rawData = $request->getBody();
        $data = json_decode($rawData, true);

        if ($data == false) {
            $this->sendJsonResponse([
                'status' => 400,
                'error' => 100,
                'message' => 'malformed request json',
                'payload' => []
            ]);
        } else {
            $proxyRequest = new ProxyRequest();
            $proxyRequest->setData($data);
            $response = $client->send($proxyRequest);

            $this->sendJsonResponse($response->getRawData());
        }
    }

    /**
     * @param array $data
     */
    protected function sendJsonResponse(array $data)
    {
        $content = json_encode($data);

        http_response_code(200);
        header(sprintf("%s: %s", "Content-Type", "application/json"));
        header(sprintf("%s: %s", "Content-Length", strlen($content)));
        echo $content;
    }
}