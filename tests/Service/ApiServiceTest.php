<?php

namespace App\Tests\Service;

use App\Service\ApiService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[AllowMockObjectsWithoutExpectations]
class ApiServiceTest extends TestCase
{
    public function testFetchData(): void
    {
        $clientMock = $this->createMock(HttpClientInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);

        $responseMock->method('toArray')->willReturn([
            'status' => 'success',
            'data' => []
        ]);

        $clientMock->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://v1.baseball.api-sports.io/',
                $this->callback(function ($options) {
                    return isset($options['headers']['Authorization'])
                        && $options['headers']['Authorization'] === 'Bearer ba058b6113f9766ce06bfb633d82aeb6'
                        && isset($options['query']['param'])
                        && $options['query']['param'] === 'valeur';
                })
            )
            ->willReturn($responseMock);

        $apiService = new ApiService($clientMock);
        $result = $apiService->fetchData();

        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('data', $result);
    }
}
