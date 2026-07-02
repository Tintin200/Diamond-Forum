<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NewsController extends AbstractController
{
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

