<?php

namespace rollun\StreetPricer\Adapters;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ClientAdapter implements ClientInterface
{
    protected $client;

    public function __construct(\GuzzleHttp\ClientInterface $client)
    {
        $this->client = $client;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->client->send($request);
        } catch (ClientExceptionInterface $exception) {
            throw $exception;
        } catch (\GuzzleHttp\Exception\ClientException $exception) {
            throw new ClientException(
                $exception->getCode(),
                $exception->getRequest(),
                $exception->getResponse(),
                $exception->getPrevious(),
                $exception->getHandlerContext()
            );
        }
    }
}