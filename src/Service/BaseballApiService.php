<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;

class BaseballApiService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        private LoggerInterface $logger,
        private string $baseballApiUrl,
        private string $baseballApiKey
    ) {}

    /**
     * Helper to perform requests to the external API-Sports Baseball API.
     *
     * @param string $endpoint The API endpoint (e.g. 'games')
     * @param array $query Query parameters
     * @param int $cacheTtl Time to live in seconds for the cache
     * @return array The response array containing the API data
     */
    private function request(string $endpoint, array $query = [], int $cacheTtl = 300): array
    {
        // Generate a cache key based on the endpoint and query parameters
        $cacheKey = 'baseball_api_' . md5($endpoint . serialize($query));

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($endpoint, $query, $cacheTtl) {
            $item->expiresAfter($cacheTtl);

            try {
                $apiUrl = rtrim($this->baseballApiUrl, '/') . '/' . ltrim($endpoint, '/');
                
                $response = $this->httpClient->request('GET', $apiUrl, [
                    'headers' => [
                        'x-apisports-key' => $this->baseballApiKey,
                        'Accept' => 'application/json',
                    ],
                    'query' => $query,
                ]);

                if ($response->getStatusCode() !== 200) {
                    $this->logger->error('Baseball API returned non-200 status code', [
                        'status_code' => $response->getStatusCode(),
                        'endpoint' => $endpoint,
                        'query' => $query,
                    ]);
                    return [];
                }

                $data = $response->toArray();
                
                // API-Sports reports errors inside a 200 OK response under the "errors" key
                if (!empty($data['errors'])) {
                    $this->logger->error('Baseball API returned errors in response body', [
                        'errors' => $data['errors'],
                        'endpoint' => $endpoint,
                        'query' => $query,
                    ]);
                    return [];
                }

                return $data['response'] ?? [];
            } catch (\Exception $e) {
                $this->logger->error('Exception occurred during Baseball API call', [
                    'message' => $e->getMessage(),
                    'endpoint' => $endpoint,
                    'query' => $query,
                ]);
                return [];
            }
        });
    }

    /**
     * Get leagues.
     *
     * Parameters: id, name, country, type, season
     */
    public function getLeagues(array $query = []): array
    {
        // Leagues change very rarely, cache for 24 hours
        return $this->request('leagues', $query, 86400);
    }

    /**
     * Get seasons.
     */
    public function getSeasons(): array
    {
        // Seasons list changes very rarely, cache for 24 hours
        return $this->request('seasons', [], 86400);
    }

    /**
     * Get teams.
     *
     * Parameters: id, name, league, season
     */
    public function getTeams(array $query): array
    {
        // Team details change rarely, cache for 12 hours
        return $this->request('teams', $query, 43200);
    }

    /**
     * Get games.
     *
     * Parameters: id, date, league, season, team, status, live, timezone
     */
    public function getGames(array $query = []): array
    {
        // Check if query is targeting live scores or today's matches
        $isLive = isset($query['live']) || (isset($query['date']) && $query['date'] === date('Y-m-d'));
        // Cache live scores for 30s, and future/past games for 10 minutes
        $ttl = $isLive ? 30 : 600;

        return $this->request('games', $query, $ttl);
    }

    /**
     * Get standings.
     *
     * Parameters: league (required), season (required), team, group
     */
    public function getStandings(array $query): array
    {
        // Standings update after match results, cache for 30 minutes
        return $this->request('standings', $query, 1800);
    }

    /**
     * Get the most recent standings dynamically (self-healing for free plans)
     */
    public function getMostRecentStandings(int $leagueId = 1): array
    {
        $currentYear = (int) date('Y');
        
        // Try years from current year down to 2022
        for ($year = $currentYear; $year >= 2022; $year--) {
            $standings = $this->getStandings(['league' => $leagueId, 'season' => $year]);
            if (!empty($standings)) {
                return [
                    'season' => $year,
                    'data' => $standings[0] ?? []
                ];
            }
        }

        return [
            'season' => 2024,
            'data' => []
        ];
    }

    /**
     * Get team statistics.
     *
     * Parameters: team (required), league (required), season (required)
     */
    public function getTeamStatistics(array $query): array
    {
        return $this->request('teams/statistics', $query, 3600);
    }
}
