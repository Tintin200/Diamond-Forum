<?php

namespace App\Tests\Service;

use App\Service\BaseballApiService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[AllowMockObjectsWithoutExpectations]
class BaseballApiServiceTest extends TestCase
{
    private $httpClientMock;
    private $cacheMock;
    private $loggerMock;
    private BaseballApiService $service;

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->service = new BaseballApiService(
            $this->httpClientMock,
            $this->cacheMock,
            $this->loggerMock,
            'https://api.example.com/',
            'test_api_key'
        );
    }

    /**
     * Helper to configure the cache mock to bypass cache and execute the callback.
     */
    private function configureCacheMockToExecuteCallback(): void
    {
        $this->cacheMock->method('get')
            ->willReturnCallback(function (string $key, callable $callback) {
                $itemMock = $this->createMock(ItemInterface::class);
                return $callback($itemMock);
            });
    }

    public function testGetLeaguesSuccess(): void
    {
        $this->configureCacheMockToExecuteCallback();

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('toArray')->willReturn([
            'response' => [
                ['id' => 1, 'name' => 'Major League Baseball']
            ]
        ]);

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.example.com/leagues',
                $this->callback(function ($options) {
                    return $options['headers']['x-apisports-key'] === 'test_api_key'
                        && isset($options['query'])
                        && $options['query']['country'] === 'USA';
                })
            )
            ->willReturn($responseMock);

        $result = $this->service->getLeagues(['country' => 'USA']);

        $this->assertCount(1, $result);
        $this->assertEquals('Major League Baseball', $result[0]['name']);
    }

    public function testRequestReturnsEmptyOnNon200Status(): void
    {
        $this->configureCacheMockToExecuteCallback();

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(500);

        $this->httpClientMock->method('request')->willReturn($responseMock);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Baseball API returned non-200 status code'));

        $result = $this->service->getSeasons();

        $this->assertEmpty($result);
    }

    public function testRequestReturnsEmptyOnApiResponseErrors(): void
    {
        $this->configureCacheMockToExecuteCallback();

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('toArray')->willReturn([
            'errors' => [
                'rate_limit' => 'You have reached your daily limit.'
            ]
        ]);

        $this->httpClientMock->method('request')->willReturn($responseMock);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Baseball API returned errors in response body'));

        $result = $this->service->getTeams(['league' => 1]);

        $this->assertEmpty($result);
    }

    public function testRequestReturnsEmptyOnException(): void
    {
        $this->configureCacheMockToExecuteCallback();

        $this->httpClientMock->method('request')
            ->willThrowException(new \Exception('Connection timeout'));

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Exception occurred during Baseball API call'));

        $result = $this->service->getGames(['live' => 'all']);

        $this->assertEmpty($result);
    }

    public function testGetMostRecentStandingsFallback(): void
    {
        // We will call getMostRecentStandings.
        // We need to configure the cache mock to call the callback for each iteration.
        // And we will configure httpClientMock to return different results depending on the year queried.
        // Let's assume current year is 2026.
        // Loop will query 2026, 2025, 2024, 2023, 2022.
        // Let's make 2026 and 2025 return empty, and 2024 return some data.
        
        $this->cacheMock->method('get')
            ->willReturnCallback(function (string $key, callable $callback) {
                $itemMock = $this->createMock(ItemInterface::class);
                return $callback($itemMock);
            });

        $responseEmpty = $this->createMock(ResponseInterface::class);
        $responseEmpty->method('getStatusCode')->willReturn(200);
        $responseEmpty->method('toArray')->willReturn([
            'response' => []
        ]);

        $responseSuccess = $this->createMock(ResponseInterface::class);
        $responseSuccess->method('getStatusCode')->willReturn(200);
        $responseSuccess->method('toArray')->willReturn([
            'response' => [
                ['rank' => 1, 'team' => ['name' => 'New York Yankees']]
            ]
        ]);

        $currentYear = (int) date('Y');

        $this->httpClientMock->method('request')
            ->willReturnCallback(function (string $method, string $url, array $options) use ($currentYear, $responseEmpty, $responseSuccess) {
                $season = $options['query']['season'] ?? null;
                if ($season === $currentYear || $season === ($currentYear - 1)) {
                    return $responseEmpty;
                }
                return $responseSuccess;
            });

        $result = $this->service->getMostRecentStandings(1);

        $this->assertEquals($currentYear - 2, $result['season']);
        $this->assertEquals('New York Yankees', $result['data']['team']['name']);
    }
}
