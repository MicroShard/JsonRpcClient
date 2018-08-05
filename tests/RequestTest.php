<?php

namespace MicroShard\JsonRpcClient\Test;

use Closure;
use MicroShard\JsonRpcClient\Client\Request;
use MicroShard\JsonRpcClient\Exception\RpcClientException;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{

    public function testNoClient()
    {

        $exception = $this->getException(function(){
            $request = new Request();
            $request->send();
        });

        $this->assertInstanceOf(RpcClientException::class, $exception);
        $this->assertEquals('unable to send request - no client set', $exception->getMessage());
    }


    /**
     * @param Closure $function
     * @return \Exception|null
     */
    public function getException(Closure $function)
    {
        $exception = null;
        try {
            $function();
        } catch (\Exception $e) {
            $exception = $e;
        }
        return $exception;
    }
}