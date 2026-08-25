<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiService
{
    public function __construct(
        private HttpClientInterface $client,
        private string $apiKey = 'ba058b6113f9766ce06bfb633d82aeb6'
    ) {}

    public function fetchData(): array
    {
        $response = $this->client->request('GET', 'https://v1.baseball.api-sports.io/', [
            'headers' => ['Authorization' => 'Bearer ' . $this->apiKey],
            'query'   => ['param' => 'valeur'],
        ]);

        return $response->toArray();
    }
}