<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    #[Route('/articles/nouveau', name: 'app_article_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Vous devez être connecté pour publier un article.');
            return $this->redirectToRoute('app_login');
        }

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $article->setAuthor($user);
            $article->setAuteur($user->getUserIdentifier());

            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'Votre article a été publié avec succès !');

            return $this->redirectToRoute('app_news');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/articles', name: 'app_articles')]
    public function list(): Response
    {
        return $this->redirectToRoute('app_news');
    }

    #[Route('/articles/{id}/modifier', name: 'app_article_edit')]
    public function edit(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Vous devez être connecté pour modifier un article.');
            return $this->redirectToRoute('app_login');
        }

        // Access check: only author or admin can edit
        if ($article->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à modifier cet article.");
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Votre article a été modifié avec succès !');

            return $this->redirectToRoute('app_news');
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    #[Route('/articles/{id}/supprimer', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Vous devez être connecté pour supprimer un article.');
            return $this->redirectToRoute('app_login');
        }

        // Access check: only author or admin can delete
        if ($article->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à supprimer cet article.");
        }

        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            // Delete associated likes and views to avoid constraint issues
            foreach ($article->getArticleLikes() as $like) {
                $entityManager->remove($like);
            }
            foreach ($article->getArticleVues() as $vue) {
                $entityManager->remove($vue);
            }

            $entityManager->remove($article);
            $entityManager->flush();

            $this->addFlash('success', 'L\'article a été supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_news');
    }
}

