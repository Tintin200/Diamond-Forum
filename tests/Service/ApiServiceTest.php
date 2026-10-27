<?php

namespace App\Tests\Service;

use App\Service\ApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiServiceTest extends TestCase
{
    public function testFetchDataIsDisabled(): void
    {
        $client = $this->createStub(HttpClientInterface::class);
        $apiService = new ApiService($client, 'fake-api-key');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('ApiService is deprecated');

        $apiService->fetchData();
    }
}