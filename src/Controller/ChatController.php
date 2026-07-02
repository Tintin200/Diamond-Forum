<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class ChatController extends AbstractController
{
    #[Route('/chat', name: 'app_chat')]
    public function index(ArticleRepository $articleRepository): Response
    {
        $recentArticles = $articleRepository->findBy([], ['id' => 'DESC'], 5);

        // Fetch post counts dynamically by tag to display in forum categories
        $mlbCount = $articleRepository->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where('a.tags LIKE :tag')
            ->setParameter('tag', '%MLB%')
            ->getQuery()
            ->getSingleScalarResult();

        $mercatoCount = $articleRepository->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where('a.tags LIKE :tag')
            ->setParameter('tag', '%Saison%')
            ->orWhere('a.tags LIKE :tag2')
            ->setParameter('tag2', '%Record%')
            ->getQuery()
            ->getSingleScalarResult();

        $guideCount = $articleRepository->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where('a.tags LIKE :tag')
            ->setParameter('tag', '%Guide%')
            ->orWhere('a.tags LIKE :tag2')
            ->setParameter('tag2', '%Stats%')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('chat/index.html.twig', [
            'recentArticles' => $recentArticles,
            'mlbCount' => $mlbCount,
            'mercatoCount' => $mercatoCount,
            'guideCount' => $guideCount,
        ]);
    }
}

