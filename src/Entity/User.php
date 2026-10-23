<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User - Entité représentant un utilisateur du système.
 * Implémente UserInterface et PasswordAuthenticatedUserInterface pour l'authentification.
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name='app_user')
 * @UniqueEntity(fields={'email'}, message='Cette adresse email est déjà utilisée.')
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'app_user')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse email est déjà utilisée par un autre compte.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Article> Articles publiés par cet utilisateur
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'author')]
    private Collection $articles;

    /**
     * @var Collection<int, ArticleLike> Likes posés par cet utilisateur
     */
    #[ORM\OneToMany(targetEntity: ArticleLike::class, mappedBy: 'user')]
    private Collection $articleLikes;

    /**
     * @var Collection<int, ArticleVue> Vues effectuées par cet utilisateur
     */
    #[ORM\OneToMany(targetEntity: ArticleVue::class, mappedBy: 'user')]
    private Collection $articleVues;

    /**
     * Constructeur - Initialise les collections pour les relations.
     * @param void
     */
    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->articleLikes = new ArrayCollection();
        $this->articleVues = new ArrayCollection();
    }

    /**
     * Retourne l'ID de l'utilisateur.
     *
     * @return int|null L'ID de l'utilisateur (null si non persiste)
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'adresse email de l'utilisateur.
     *
     * @return string|null L'adresse email
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Définit l'adresse email de l'utilisateur.
     *
     * @param string $email La nouvelle adresse email
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Retourne un identifiant visuel pour cet utilisateur. Implémente UserInterface::getUserIdentifier().
     * @return string L'email de l'utilisateur
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Retourne les rôles de l'utilisateur. Implémente UserInterface::getRoles().
     * Garantit que chaque utilisateur a au moins le rôle ROLE_USER.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Garantit que chaque utilisateur a au moins le rôle ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Définit les rôles de l'utilisateur. Note : ROLE_USER sera ajouté automatiquement dans getRoles().
     * @param array $roles Liste des rôles à attribuer
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Retourne le mot de passe haché. Implémente PasswordAuthenticatedUserInterface::getPassword().
     * @return string|null Le mot de passe haché
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Définit le mot de passe haché.
     * IMPORTANT : Cette méthode attend un mot de passe déjà haché.
     * @param string $password Le mot de passe haché
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Sérialise l'utilisateur en masquant le hash du mot de passe.
     * Remplace le hash par CRC32C avant sérialisation.
     * @return array Données sérialisées avec le mot de passe masqué
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    /**
     * Désérialise l'utilisateur.
     * Gère la restauration des propriétés privées (avec les null bytes dans les clés).
     * @param array $data Données sérialisées
     */
    public function __unserialize(array $data): void
    {
        foreach ($data as $key => $value) {
            // Gère les propriétés privées avec des null bytes dans la clé
            if (str_starts_with($key, "\0")) {
                $parts = explode("\0", $key, 3);
                if (count($parts) === 3) {
                    $property = $parts[2];
                    $this->{$property} = $value;
                }
            } else {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Retourne la collection des articles publiés par cet utilisateur.
     *
     * @return Collection<int, Article> Tous les articles publiés
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    /**
     * Ajoute un article à la collection de l'utilisateur. Gère la relation bidirectionnelle avec Article.
     * @param Article $article L'article à ajouter
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setAuthor($this);
        }

        return $this;
    }

    /**
     * Retire un article de la collection de l'utilisateur. Gère la relation bidirectionnelle avec Article.
     * @param Article $article L'article à retirer
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            // Met à jour le côté possesseur de la relation
            if ($article->getAuthor() === $this) {
                $article->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * Retourne la collection des likes posés par cet utilisateur.
     *
     * @return Collection<int, ArticleLike> Tous les likes de l'utilisateur
     */
    public function getArticleLikes(): Collection
    {
        return $this->articleLikes;
    }

    /**
     * Ajoute un like à la collection de l'utilisateur. Gère la relation bidirectionnelle avec ArticleLike.
     * @param ArticleLike $articleLike Le like à ajouter
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function addArticleLike(ArticleLike $articleLike): static
    {
        if (!$this->articleLikes->contains($articleLike)) {
            $this->articleLikes->add($articleLike);
            $articleLike->setUser($this);
        }

        return $this;
    }

    /**
     * Retire un like de la collection de l'utilisateur. Gère la relation bidirectionnelle avec ArticleLike.
     * @param ArticleLike $articleLike Le like à retirer
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function removeArticleLike(ArticleLike $articleLike): static
    {
        if ($this->articleLikes->removeElement($articleLike)) {
            // Met à jour le côté possesseur de la relation
            if ($articleLike->getUser() === $this) {
                $articleLike->setUser(null);
            }
        }

        return $this;
    }

    /**
     * Retourne la collection des vues effectuées par cet utilisateur.
     *
     * @return Collection<int, ArticleVue> Toutes les vues de l'utilisateur
     */
    public function getArticleVues(): Collection
    {
        return $this->articleVues;
    }

    /**
     * Ajoute une vue à la collection de l'utilisateur. Gère la relation bidirectionnelle avec ArticleVue.
     * @param ArticleVue $articleVue La vue à ajouter
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function addArticleVue(ArticleVue $articleVue): static
    {
        if (!$this->articleVues->contains($articleVue)) {
            $this->articleVues->add($articleVue);
            $articleVue->setUser($this);
        }

        return $this;
    }

    /**
     * Retire une vue de la collection de l'utilisateur. Gère la relation bidirectionnelle avec ArticleVue.
     * @param ArticleVue $articleVue La vue à retirer
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function removeArticleVue(ArticleVue $articleVue): static
    {
        if ($this->articleVues->removeElement($articleVue)) {
            // Met à jour le côté possesseur de la relation
            if ($articleVue->getUser() === $this) {
                $articleVue->setUser(null);
            }
        }

        return $this;
    }
}

