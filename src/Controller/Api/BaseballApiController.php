<?php

namespace App\Controller\Api;

use App\Service\BaseballApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/external', name: 'api_external_')]
class BaseballApiController extends AbstractController
{
    public function __construct(
        private BaseballApiService $baseballApiService
    ) {}

    #[Route('/games', name: 'games', methods: ['GET'])]
    public function getGames(Request $request): JsonResponse
    {
        $query = [
            'date' => $request->query->get('date'),
            'league' => 1, // Enforce MLB only
            'season' => $request->query->get('season'),
            'live' => $request->query->get('live'),
        ];

        // Filter out empty parameters
        $query = array_filter($query, fn($value) => $value !== null && $value !== '');

        // If no filter is set, default to today's games
        if (empty($query)) {
            $query['date'] = date('Y-m-d');
        }

        $games = $this->baseballApiService->getGames($query);

        return new JsonResponse($games);
    }

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
            'league' => 1, // Enforce MLB only
            'season' => $season,
        ]);

        return new JsonResponse($standings);
    }
}
