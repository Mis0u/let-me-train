<?php

namespace App\Helper\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class LocalisationService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $url
     * @return array <string>
     */
    public function getApi(string $url): array
    {
        $response = $this->httpClient->request('GET', $url);

        return $response->toArray();
    }
}
