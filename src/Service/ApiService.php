<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiService
{
    public function __construct(private HttpClientInterface $client) {}

    public function fetchData(): array
    {
        $response = $this->client->request('GET', 'https://v1.baseball.api-sports.io/', [
            'headers' => ['Authorization' => 'Bearer ba058b6113f9766ce06bfb633d82aeb6'],
            'query'   => ['param' => 'valeur'],
        ]);

        return $response->toArray();
    }
}