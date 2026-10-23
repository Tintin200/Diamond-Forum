<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * ArticleController - Gère le CRUD des articles avec vérification d'authentification.
 * Seuls l'auteur ou un admin peuvent modifier/supprimer un article.
 */
class ArticleController extends AbstractController
{
    /**
     * Crée un nouvel article. Vérifie l'authentification, traite le formulaire et persiste l'article.
     * @param Request $request Requête HTTP contenant les données du formulaire
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités pour persister l'article
     */
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

    /**
     * Redirige /articles vers /news pour maintenir la compatibilité des liens.
     * @return Response Redirection permanente vers app_news
     */
    #[Route('/articles', name: 'app_articles')]
    public function list(): Response
    {
        return $this->redirectToRoute('app_news');
    }

    /**
     * Modifie un article existant. Vérifie l'authentification, les droits (auteur/admin) et traite le formulaire.
     * @param Article $article Article à modifier
     * @param Request $request Requête HTTP contenant les données du formulaire
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités
     * @return Response Redirection vers app_news ou rendu du formulaire
     */
    #[Route('/articles/{id}/modifier', name: 'app_article_edit')]
    public function edit(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Vous devez être connecté pour modifier un article.');
            return $this->redirectToRoute('app_login');
        }

        // Check if article has an author
        if ($article->getAuthor() === null) {
            return new Response('Article sans auteur', Response::HTTP_BAD_REQUEST);
        }

        // Access check: only author or admin can edit
        $currentUser = $this->getUser();
        $isAdmin = $currentUser && in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
        $isOwner = $article->getAuthor()->getId() === $currentUser->getId();
        
        if (!$isOwner && !$isAdmin) {
            return new Response('Accès interdit', Response::HTTP_FORBIDDEN);
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

    /**
     * Affiche un article individuel.
     * @param Article $article Article à afficher
     * @return Response Page de détail de l'article rendue avec Twig
     */
    #[Route('/articles/{id}', name: 'app_article_show')]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * Supprime un article et ses dépendances (likes, vues). Vérifie droits (auteur/admin) et token CSRF.
     * @param Article $article Article à supprimer
     * @param Request $request Contient le token CSRF
     * @param EntityManagerInterface $entityManager Pour supprimer les entités
     * @return Response Redirection vers app_news avec message flash
     */
    #[Route('/articles/{id}/supprimer', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Vous devez être connecté pour supprimer un article.');
            return $this->redirectToRoute('app_login');
        }

        // Check if article has an author
        if ($article->getAuthor() === null) {
            return new Response('Article sans auteur', Response::HTTP_BAD_REQUEST);
        }

        // Access check: only author or admin can delete
        $currentUser = $this->getUser();
        $isAdmin = $currentUser && in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
        $isOwner = $article->getAuthor()->getId() === $currentUser->getId();
        
        if (!$isOwner && !$isAdmin) {
            return new Response('Accès interdit', Response::HTTP_FORBIDDEN);
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

