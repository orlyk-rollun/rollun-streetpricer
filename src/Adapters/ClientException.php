<?php

namespace rollun\StreetPricer\Adapters;

use Psr\Http\Client\ClientExceptionInterface;

class ClientException extends \GuzzleHttp\Exception\ClientException implements ClientExceptionInterface
{

}