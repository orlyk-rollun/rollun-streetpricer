<?php

namespace rollun\StreetPricer;

use Exception;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\SimpleCache\CacheInterface;

class Client
{
    protected $baseUri;

    protected $client;

    protected $token;

    public function __construct(
        ClientInterface $client = null
    ) {
        $this->client = $client ?? new \GuzzleHttp\Client();
    }

    public function setBaseUri($uri)
    {
        $this->baseUri = $uri;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function get($path, array $query = [])
    {
        $request = new Request('GET', $this->makeUri($path, $query));

        return $this->send($request);
    }

    public function post($path, array $data = [])
    {
        $request = new Request('POST', $this->makeUri($path));
        $request = $request->withBody(new MultipartStream($this->prepareBody($data)));

        return $this->send($request);
    }

    public function put($path, array $data = [])
    {
        $request = new Request('PUT', $this->makeUri($path));

        return $this->send($request, $this->makeFormParams($data));
    }

    public function send(Request $request)
    {
        if ($this->token) {
            $request = $request->withHeader('Authorization', 'Bearer ' . $this->token);
        }
        
        $response = $this->client->sendRequest($request);
        
        if (in_array($response->getStatusCode(), [200, 201])) {
            $content = $response->getBody()->getContents();
            $data = json_decode($content, true);
            return $data;
        }

        throw new Exception('Response error');
    }

    protected function prepareBody($data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[] = [
                'name' => $key,
                'contents' => $value
            ];
        }

        return $result;
    }

    protected function makeUri($path, $query = [])
    {
        $uri = rtrim($this->baseUri, '/') . '/' . ltrim($path, '/');
        if ($query) {
            $uri .= '?' . http_build_query($query);
        }

        return $uri;
    }

    protected function makeFormParams(array $data = [])
    {
        $options = [];
        if (!empty($data)) {
            $options = ['form_params' => $data];
        }

        return $options;
    }

    /*protected function createDefaultStorage()
    {
        $storage = StorageFactory::factory([
            'adapter' => [
                'name' => 'filesystem',
                'options' => [
                    'cacheDir' => 'data/cache',
                    'ttl' => 7200,
                ]
            ],
            'plugins' => [
                'exception_handler' => [
                    'throw_exceptions' => false
                ],
                'serializer'
            ],
        ]);

        return new SimpleCacheDecorator($storage);
    }*/
}