<?php

namespace App\Helper\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class LocalisationService
{
    private HttpClientInterface $httpClient;
    public const LOCALISATION = 'http://ip-api.com/json';

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return array <string>
     */
    public function getApi(): array
    {
        $response = $this->httpClient->request('GET', self::LOCALISATION);

        return $response->toArray();
    }
}
