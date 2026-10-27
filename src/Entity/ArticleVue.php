<?php

namespace App\Entity;

use App\Repository\ArticleVueRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * ArticleVue - Entité représentant une vue d'un article.
 * Stocke la relation entre un utilisateur et un article pour le suivi des vues.
 * @ORM\Entity(repositoryClass=ArticleVueRepository::class)
 */
#[ORM\Entity(repositoryClass: ArticleVueRepository::class)]
class ArticleVue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var User|null L'utilisateur qui a vu l'article
     */
    #[ORM\ManyToOne(inversedBy: 'articleVues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Article|null L'article qui a été vu
     */
    #[ORM\ManyToOne(inversedBy: 'articleVues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    /**
     * Retourne l'ID de la vue.
     *
     * @return int|null L'ID (null si non persiste)
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'utilisateur qui a vu l'article.
     *
     * @return User|null L'utilisateur
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Définit l'utilisateur qui a vu l'article.
     *
     * @param User|null $user L'utilisateur
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Retourne l'article qui a été vu.
     *
     * @return Article|null L'article
     */
    public function getArticle(): ?Article
    {
        return $this->article;
    }

    /**
     * Définit l'article qui a été vu.
     *
     * @param Article|null $article L'article
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }
}
