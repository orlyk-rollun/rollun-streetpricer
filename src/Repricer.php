<?php

namespace rollun\StreetPricer;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\SimpleCache\CacheInterface;

class Repricer
{
    public const TOKEN_CACHE_KEY = 'replicer_token';

    public const BASE_URI = 'https://api.streetpricer.com/api/v1/';

    protected $username;
    
    protected $password;

    protected $client;

    protected $cache;

    public function __construct(
        string $username,
        string $password,
        Client $client = null,
        CacheInterface $cache = null
    ) {
        $this->username = $username;
        $this->password = $password;

        if (empty($client)) {
            $client = new Client();
        }
        $this->client = $client;
        $this->client->setBaseUri(self::BASE_URI);

        $this->cache = $cache;

        $token = $this->auth();
        $this->client->setToken($token);
    }
    
    public function auth()
    {
        if ($this->cache && $this->cache->has(self::TOKEN_CACHE_KEY)) {
            //return $this->cache->get(self::TOKEN_CACHE_KEY);
        }

        $response = $this->client->post('auth/token', [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if (isset($response->token) && $this->cache) {
            $this->cache->set(self::TOKEN_CACHE_KEY, $response->token);
        }

        return $response['token'];
    }

    /*public function getStores()
    {
        $path = 'stores/';
        return $this->client->get($path);
    }*/

    /*public function getStore($storeId)
    {
        $path = 'stores/' . $storeId;
        return $this->client->get($path);
    }*/

    /*public function updateStore($storeId, $aIScan = 0, $amazon = 0, $autoPrice = 0, $autoLock = 0)
    {
        $path = 'stores/' . $storeId;
        $this->client->post($path, [
            'AIScan' => $aIScan,
            'Amazon' => $amazon,
            'AutoPrice' => $autoPrice,
            'Autolock' => $autoLock
        ]);
    }*/

    /**
     * @param $userId
     * @todo add pagination
     */
    public function getProducts($userId)
    {
        $path = 'stores/' . $userId . '/items';
        $response = $this->client->get($path);

        return $response;
    }

    public function getItem($userId, $itemId, $competitorDetail = 0)
    {
        $path = 'stores/' . $userId . '/items/' . $itemId;
        
        try {
            $response = $this->client->get($path, [
                'CompetitorDetail' => $competitorDetail
            ]);
        } catch (ClientExceptionInterface $exception) {
            if ($exception->getCode() == 404) {
                return null;
            }
        }

        return $response[0];
    }

    /**
     * @param $userId
     * @param $itemId
     * @return void
     * @todo
     */
    public function updateItem($userId, $itemId, $data)
    {
        $path = 'stores/' . $userId . '/items/' . $itemId;

        return $this->client->put($path, $data);
    }

    public function addItem($userId, $data)
    {
        $token = $this->auth();
        $this->client->setToken($token);

        $path = 'stores/' . $userId . '/items';

        return $this->client->post($path, $data);
    }
}