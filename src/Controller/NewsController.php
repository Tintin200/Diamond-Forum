<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * NewsController - Affiche les articles sous forme de fil d'actualité.
 * Sépare en "À la Une" (3 plus récents) et "Publications de la Communauté".
 */
final class NewsController extends AbstractController
{
    /**
     * Affiche la page des actualités avec tous les articles.
     * @param ArticleRepository $articleRepository Repository pour récupérer les articles
     * @return Response Page des actualités rendue avec Twig
     */
    #[Route('/news', name: 'app_news')]
    public function index(ArticleRepository $articleRepository): Response
    {
        $allArticles = $articleRepository->findBy([], ['id' => 'DESC']);

        // Split articles: the 3 most recent go to "À la Une", the rest go to "Publications de la Communauté"
        $headlineArticles = array_slice($allArticles, 0, 3);
        $communityArticles = array_slice($allArticles, 3);

        return $this->render('news/index.html.twig', [
            'headlineArticles' => $headlineArticles,
            'communityArticles' => $communityArticles,
        ]);
    }
}

