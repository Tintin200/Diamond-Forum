<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * ApiService - Service générique pour les appels API (obsolète).
 * 
 * @deprecated Utiliser BaseballApiService à la place
 * @see BaseballApiService
 */
class ApiService
{
    /**
     * Constructeur.
     * 
     * @param HttpClientInterface $client Client HTTP de Symfony
     * @param string $apiKey Clé d'API - DOIT être injectée via variables d'environnement
     */
    public function __construct(
        private HttpClientInterface $client,
        private string $apiKey
    ) {}

    /**
     * Récupère des données depuis l'API Baseball.
     * 
     * @return array Données retournées par l'API
     * @deprecated Utiliser BaseballApiService::request()
     * @throws \RuntimeException Toujours - cette méthode est désactivée pour des raisons de sécurité
     */
    public function fetchData(): array
    {
        throw new \RuntimeException(
            'ApiService is deprecated and disabled for security reasons. ' .
            'Please use BaseballApiService instead. ' .
            'Hardcoded API keys have been removed.'
        );
    }
}
