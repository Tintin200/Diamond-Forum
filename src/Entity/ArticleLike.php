<?php

namespace App\Entity;

use App\Repository\ArticleLikeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * ArticleLike - Entité représentant un "like" sur un article.
 * Stocke la relation entre un utilisateur et un article.
 * @ORM\Entity(repositoryClass=ArticleLikeRepository::class)
 */
#[ORM\Entity(repositoryClass: ArticleLikeRepository::class)]
class ArticleLike
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var User|null L'utilisateur qui a liké l'article
     */
    #[ORM\ManyToOne(inversedBy: 'articleLikes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Article|null L'article qui a été liké
     */
    #[ORM\ManyToOne(inversedBy: 'articleLikes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    /**
     * @var \DateTime|null Date et heure de création du like
     */
    #[ORM\Column]
    private ?\DateTime $created_at = null;

    /**
     * Retourne l'ID du like.
     *
     * @return int|null L'ID (null si non persiste)
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'utilisateur qui a liké.
     *
     * @return User|null L'utilisateur
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Définit l'utilisateur qui a liké.
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
     * Retourne l'article qui a été liké.
     *
     * @return Article|null L'article
     */
    public function getArticle(): ?Article
    {
        return $this->article;
    }

    /**
     * Définit l'article qui a été liké.
     *
     * @param Article|null $article L'article
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Retourne la date de création du like.
     *
     * @return \DateTime|null La date et heure de création
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    /**
     * Définit la date de création du like.
     *
     * @param \DateTime $created_at La date et heure de création
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setCreatedAt(\DateTime $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
