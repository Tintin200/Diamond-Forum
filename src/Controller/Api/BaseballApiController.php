<?php

namespace App\Controller\Api;

use App\Service\BaseballApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * BaseballApiController - Fournit des endpoints REST pour accéder aux données de l'API Baseball.
 * Endpoints : GET /api/external/games, /leagues, /standings.
 */
#[Route('/api/external', name: 'api_external_')]
class BaseballApiController extends AbstractController
{
    /**
     * Constructeur - Injecte le service BaseballApiService.
     * @param BaseballApiService $baseballApiService Service pour l'API Baseball
     */
    public function __construct(
        private BaseballApiService $baseballApiService
    ) {}

    /**
     * Récupère les matchs de baseball. Force league=1 (MLB).
     * @param Request $request Requête HTTP contenant les paramètres de filtrage
     * @return JsonResponse Liste des matchs au format JSON
     */
    #[Route('/games', name: 'games', methods: ['GET'])]
    public function getGames(Request $request): JsonResponse
    {
        $query = [
            'date' => $request->query->get('date'),
            'league' => 1, // Force MLB uniquement
            'season' => $request->query->get('season'),
            'live' => $request->query->get('live'),
        ];

        // Filtre les paramètres vides
        $query = array_filter($query, fn($value) => $value !== null && $value !== '');

        // Si aucun filtre n'est défini, utilise les matchs du jour par défaut
        if (empty($query)) {
            $query['date'] = date('Y-m-d');
        }

        $games = $this->baseballApiService->getGames($query);

        return new JsonResponse($games);
    }

    /**
     * Récupère les ligues de baseball.
     * @param Request $request Requête HTTP contenant les paramètres de filtrage
     * @return JsonResponse Liste des ligues au format JSON
     */
    #[Route('/leagues', name: 'leagues', methods: ['GET'])]
    public function getLeagues(Request $request): JsonResponse
    {
        $query = [
            'id' => $request->query->get('id'),
            'name' => $request->query->get('name'),
            'country' => $request->query->get('country'),
            'season' => $request->query->get('season'),
        ];

        $query = array_filter($query, fn($value) => $value !== null && $value !== '');

        $leagues = $this->baseballApiService->getLeagues($query);

        return new JsonResponse($leagues);
    }

    /**
     * Récupère les classements des ligues de baseball. Force league=1 (MLB).
     * @param Request $request Requête HTTP contenant le paramètre 'season'
     * @return JsonResponse Classement au format JSON
     * @throws JsonResponse HTTP 400 si 'season' n'est pas fourni
     */
    #[Route('/standings', name: 'standings', methods: ['GET'])]
    public function getStandings(Request $request): JsonResponse
    {
        $season = $request->query->get('season');

        if (!$season) {
            return new JsonResponse([
                'error' => 'Parameter "season" is required.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $standings = $this->baseballApiService->getStandings([
            'league' => 1, // Force MLB uniquement
            'season' => $season,
        ]);

        return new JsonResponse($standings);
    }
}
