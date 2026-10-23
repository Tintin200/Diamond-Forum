<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseballApiService - Service pour interagir avec l'API Baseball (API-Sports).
 * Centralise les requêtes (matchs, ligues, saisons, équipes, classements, statistiques) avec cache et gestion des erreurs.
 */
class BaseballApiService
{
    /**
     * Constructeur - Injecte les dépendances nécessaires.
     * @param HttpClientInterface $httpClient Client HTTP
     * @param CacheInterface $cache Système de cache
     * @param LoggerInterface $logger Pour logger les erreurs
     * @param string $baseballApiUrl URL de base de l'API Baseball
     * @param string $baseballApiKey Clé d'API pour l'authentification
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        private LoggerInterface $logger,
        private string $baseballApiUrl,
        private string $baseballApiKey
    ) {}

    /**
     * Effectue une requête générique vers l'API Baseball avec cache et gestion des erreurs.
     * @param string $endpoint Point d'accès de l'API
     * @param array $query Paramètres de requête
     * @param int $cacheTtl Durée de vie du cache en secondes
     * @return array Données retournées par l'API (ou tableau vide en cas d'erreur)
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
     * Récupère la liste des ligues de baseball.
     * @param array $query Paramètres de filtrage (optionnel)
     * @return array Liste des ligues correspondantes
     */
    public function getLeagues(array $query = []): array
    {
        // Les ligues changent très rarement, cache pour 24 heures
        return $this->request('leagues', $query, 86400);
    }

    /**
     * Récupère la liste des saisons disponibles.
     * @return array Liste de toutes les saisons disponibles
     */
    public function getSeasons(): array
    {
        // La liste des saisons change très rarement, cache pour 24 heures
        return $this->request('seasons', [], 86400);
    }

    /**
     * Récupère la liste des équipes de baseball.
     * @param array $query Paramètres de filtrage
     * @return array Liste des équipes correspondantes
     */
    public function getTeams(array $query): array
    {
        // Les détails des équipes changent rarement, cache pour 12 heures
        return $this->request('teams', $query, 43200);
    }

    /**
     * Récupère la liste des matchs de baseball.
     * @param array $query Paramètres de filtrage (optionnel)
     * @return array Liste des matchs correspondants
     */
    public function getGames(array $query = []): array
    {
        // Vérifie si la requête cible les matchs en direct ou les matchs du jour
        $isLive = isset($query['live']) || (isset($query['date']) && $query['date'] === date('Y-m-d'));
        // Cache les scores en direct pour 30s, et les matchs passés/futurs pour 10 minutes
        $ttl = $isLive ? 30 : 600;

        return $this->request('games', $query, $ttl);
    }

    /**
     * Récupère les classements des ligues de baseball.
     * @param array $query Paramètres de filtrage (league et season sont requis)
     * @return array Classement correspondant
     */
    public function getStandings(array $query): array
    {
        // Les classements sont mis à jour après les résultats des matchs, cache pour 30 minutes
        return $this->request('standings', $query, 1800);
    }

    /**
     * Récupère les classements les plus récents dynamiquement.
     * Teste les années de l'année courante à 2022 jusqu'à trouver des données valides.
     * @param int $leagueId ID de la ligue (défaut: 1 pour MLB)
     * @return array Tableau contenant 'season' et 'data'
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
     * Récupère les statistiques d'une équipe.
     * @param array $query Paramètres de filtrage (team, league et season sont requis)
     * @return array Statistiques de l'équipe
     */
    public function getTeamStatistics(array $query): array
    {
        // Cache pour 1 heure (3600 secondes)
        return $this->request('teams/statistics', $query, 3600);
    }
}
