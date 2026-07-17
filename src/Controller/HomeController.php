<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        \App\Service\BaseballApiService $baseballApiService,
        \App\Repository\UserRepository $userRepository,
        \App\Repository\ArticleRepository $articleRepository,
        \App\Repository\ArticleLikeRepository $articleLikeRepository
    ): Response
    {
        // Try to fetch today's MLB games from the API (League ID 1)
        $games = $baseballApiService->getGames([
            'league' => 1,
            'date' => date('Y-m-d')
        ]);

        // Fallback mock games if API key is not configured or returns empty results
        if (empty($games)) {
            $games = [
                [
                    'id' => 101,
                    'status' => ['long' => 'In Progress', 'short' => 'LIVE'],
                    'league' => [
                        'name' => 'MLB',
                        'logo' => 'https://media.api-sports.io/baseball/leagues/1.png'
                    ],
                    'teams' => [
                        'home' => ['name' => 'New York Yankees', 'logo' => 'https://media.api-sports.io/baseball/teams/1.png'],
                        'away' => ['name' => 'Boston Red Sox', 'logo' => 'https://media.api-sports.io/baseball/teams/2.png']
                    ],
                    'scores' => [
                        'home' => ['total' => 4],
                        'away' => ['total' => 3]
                    ],
                    'time' => 'LIVE (6th Inning)'
                ],
                [
                    'id' => 102,
                    'status' => ['long' => 'Not Started', 'short' => 'NS'],
                    'league' => [
                        'name' => 'MLB',
                        'logo' => 'https://media.api-sports.io/baseball/leagues/1.png'
                    ],
                    'teams' => [
                        'home' => ['name' => 'Los Angeles Dodgers', 'logo' => 'https://media.api-sports.io/baseball/teams/3.png'],
                        'away' => ['name' => 'San Francisco Giants', 'logo' => 'https://media.api-sports.io/baseball/teams/4.png']
                    ],
                    'scores' => [
                        'home' => ['total' => null],
                        'away' => ['total' => null]
                    ],
                    'time' => '22:10'
                ],
                [
                    'id' => 103,
                    'status' => ['long' => 'Finished', 'short' => 'FT'],
                    'league' => [
                        'name' => 'MLB',
                        'logo' => 'https://media.api-sports.io/baseball/leagues/1.png'
                    ],
                    'teams' => [
                        'home' => ['name' => 'Chicago Cubs', 'logo' => 'https://media.api-sports.io/baseball/teams/5.png'],
                        'away' => ['name' => 'St. Louis Cardinals', 'logo' => 'https://media.api-sports.io/baseball/teams/6.png']
                    ],
                    'scores' => [
                        'home' => ['total' => 2],
                        'away' => ['total' => 5]
                    ],
                    'time' => 'Terminé'
                ]
            ];
        }

        $standingsResult = $baseballApiService->getMostRecentStandings();
        $season = $standingsResult['season'];
        $standingsData = $standingsResult['data'];

        $americanLeague = [];
        $nationalLeague = [];

        if (!empty($standingsData)) {
            foreach ($standingsData as $item) {
                $groupName = $item['group']['name'] ?? '';
                if (str_contains($groupName, 'American')) {
                    $americanLeague[] = $item;
                } else {
                    $nationalLeague[] = $item;
                }
            }
            usort($americanLeague, fn($a, $b) => $a['position'] <=> $b['position']);
            usort($nationalLeague, fn($a, $b) => $a['position'] <=> $b['position']);
        }

        if (empty($americanLeague) && empty($nationalLeague)) {
            $season = 2024;
            $americanLeague = [
                ['position' => 1, 'team' => ['name' => 'New York Yankees', 'logo' => 'https://media.api-sports.io/baseball/teams/25.png'], 'games' => ['win' => ['total' => 94], 'lose' => ['total' => 68], 'win' => ['percentage' => '.580']]],
                ['position' => 2, 'team' => ['name' => 'Cleveland Guardians', 'logo' => 'https://media.api-sports.io/baseball/teams/9.png'], 'games' => ['win' => ['total' => 92], 'lose' => ['total' => 69], 'win' => ['percentage' => '.571']]],
                ['position' => 3, 'team' => ['name' => 'Baltimore Orioles', 'logo' => 'https://media.api-sports.io/baseball/teams/4.png'], 'games' => ['win' => ['total' => 91], 'lose' => ['total' => 71], 'win' => ['percentage' => '.562']]],
                ['position' => 4, 'team' => ['name' => 'Houston Astros', 'logo' => 'https://media.api-sports.io/baseball/teams/15.png'], 'games' => ['win' => ['total' => 88], 'lose' => ['total' => 73], 'win' => ['percentage' => '.547']]],
                ['position' => 5, 'team' => ['name' => 'Kansas City Royals', 'logo' => 'https://media.api-sports.io/baseball/teams/16.png'], 'games' => ['win' => ['total' => 86], 'lose' => ['total' => 76], 'win' => ['percentage' => '.531']]],
                ['position' => 6, 'team' => ['name' => 'Detroit Tigers', 'logo' => 'https://media.api-sports.io/baseball/teams/11.png'], 'games' => ['win' => ['total' => 86], 'lose' => ['total' => 76], 'win' => ['percentage' => '.531']]],
            ];
            $nationalLeague = [
                ['position' => 1, 'team' => ['name' => 'Los Angeles Dodgers', 'logo' => 'https://media.api-sports.io/baseball/teams/18.png'], 'games' => ['win' => ['total' => 98], 'lose' => ['total' => 64], 'win' => ['percentage' => '.605']]],
                ['position' => 2, 'team' => ['name' => 'Philadelphia Phillies', 'logo' => 'https://media.api-sports.io/baseball/teams/26.png'], 'games' => ['win' => ['total' => 95], 'lose' => ['total' => 67], 'win' => ['percentage' => '.586']]],
                ['position' => 3, 'team' => ['name' => 'Milwaukee Brewers', 'logo' => 'https://media.api-sports.io/baseball/teams/19.png'], 'games' => ['win' => ['total' => 93], 'lose' => ['total' => 69], 'win' => ['percentage' => '.574']]],
                ['position' => 4, 'team' => ['name' => 'San Diego Padres', 'logo' => 'https://media.api-sports.io/baseball/teams/29.png'], 'games' => ['win' => ['total' => 93], 'lose' => ['total' => 69], 'win' => ['percentage' => '.574']]],
                ['position' => 5, 'team' => ['name' => 'Atlanta Braves', 'logo' => 'https://media.api-sports.io/baseball/teams/2.png'], 'games' => ['win' => ['total' => 89], 'lose' => ['total' => 73], 'win' => ['percentage' => '.549']]],
                ['position' => 6, 'team' => ['name' => 'New York Mets', 'logo' => 'https://media.api-sports.io/baseball/teams/24.png'], 'games' => ['win' => ['total' => 89], 'lose' => ['total' => 73], 'win' => ['percentage' => '.549']]],
            ];
        } else {
            $americanLeague = array_slice($americanLeague, 0, 6);
            $nationalLeague = array_slice($nationalLeague, 0, 6);
        }

        $userCount = $userRepository->count([]);
        $articleCount = $articleRepository->count([]);
        $likeCount = $articleLikeRepository->count([]);

        $latestArticles = $articleRepository->findBy([], ['id' => 'DESC'], 3);

        return $this->render('home/index.html.twig', [
            'games' => array_slice($games, 0, 3),
            'americanLeague' => $americanLeague,
            'nationalLeague' => $nationalLeague,
            'standingsSeason' => $season,
            'userCount' => $userCount,
            'articleCount' => $articleCount,
            'likeCount' => $likeCount,
            'latestArticles' => $latestArticles,
        ]);
    }
}
