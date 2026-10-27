<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Article - Entité représentant un article publié sur le site.
 * Stocke les informations d'un article publié par un utilisateur.
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 */
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $sujet = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    #[ORM\Column(length: 150)]
    private ?string $auteur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tags = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    /**
     * @var Collection<int, ArticleLike>
     */
    #[ORM\OneToMany(targetEntity: ArticleLike::class, mappedBy: 'article')]
    private Collection $articleLikes;

    /**
     * @var Collection<int, ArticleVue>
     */
    #[ORM\OneToMany(targetEntity: ArticleVue::class, mappedBy: 'article')]
    private Collection $articleVues;

    /**
     * Constructeur - Initialise les collections pour les relations.
     * @param void
     */
    public function __construct()
    {
        $this->articleLikes = new ArrayCollection();
        $this->articleVues = new ArrayCollection();
    }

    /**
     * Retourne l'ID de l'article.
     * @return int|null L'ID de l'article (null si non persiste)
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le sujet (titre) de l'article.
     * @return string|null Le titre de l'article
     */
    public function getSujet(): ?string
    {
        return $this->sujet;
    }

    /**
     * Définit le sujet (titre) de l'article.
     * @param string $sujet Le nouveau titre
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setSujet(string $sujet): static
    {
        $this->sujet = $sujet;

        return $this;
    }

    /**
     * Retourne le contenu de l'article.
     * @return string|null Le contenu de l'article
     */
    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    /**
     * Définit le contenu de l'article.
     * @param string $contenu Le nouveau contenu
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Retourne le nom de l'auteur (texte). Champ redondant avec author pour éviter le lazy loading.
     * @return string|null Le nom de l'auteur
     */
    public function getAuteur(): ?string
    {
        return $this->auteur;
    }

    /**
     * Définit le nom de l'auteur (texte).
     *
     * @param string $auteur Le nom de l'auteur
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setAuteur(string $auteur): static
    {
        $this->auteur = $auteur;

        return $this;
    }

    /**
     * Retourne l'URL de l'image de couverture.
     *
     * @return string|null L'URL de l'image (null si pas d'image)
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * Définit l'URL de l'image de couverture.
     *
     * @param string|null $image L'URL de l'image
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Retourne les tags/catégories de l'article. Format : tags séparés par des virgules.
     * @return string|null Les tags de l'article
     */
    public function getTags(): ?string
    {
        return $this->tags;
    }

    /**
     * Définit les tags/catégories de l'article.
     *
     * @param string|null $tags Les tags séparés par des virgules
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setTags(?string $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Retourne l'auteur de l'article (entité User). Relation ManyToOne.
     * @return User|null L'utilisateur qui a publié l'article
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Définit l'auteur de l'article.
     *
     * @param User|null $author L'utilisateur auteur
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Retourne la collection des likes pour cet article.
     *
     * @return Collection<int, ArticleLike> Collection de tous les likes reçus
     */
    public function getArticleLikes(): Collection
    {
        return $this->articleLikes;
    }

    /**
     * Ajoute un like à cet article. Gère la relation bidirectionnelle avec ArticleLike.
     * @param ArticleLike $articleLike Le like à ajouter
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function addArticleLike(ArticleLike $articleLike): static
    {
        if (!$this->articleLikes->contains($articleLike)) {
            $this->articleLikes->add($articleLike);
            $articleLike->setArticle($this);
        }

        return $this;
    }

    /**
     * Retire un like de cet article. Gère la relation bidirectionnelle avec ArticleLike.
     * @param ArticleLike $articleLike Le like à retirer
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function removeArticleLike(ArticleLike $articleLike): static
    {
        if ($this->articleLikes->removeElement($articleLike)) {
            // Met à jour le côté possesseur de la relation
            if ($articleLike->getArticle() === $this) {
                $articleLike->setArticle(null);
            }
        }

        return $this;
    }

    /**
     * Retourne la collection des vues pour cet article.
     *
     * @return Collection<int, ArticleVue> Collection de toutes les vues enregistrées
     */
    public function getArticleVues(): Collection
    {
        return $this->articleVues;
    }

    /**
     * Ajoute une vue à cet article. Gère la relation bidirectionnelle avec ArticleVue.
     * @param ArticleVue $articleVue La vue à ajouter
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function addArticleVue(ArticleVue $articleVue): static
    {
        if (!$this->articleVues->contains($articleVue)) {
            $this->articleVues->add($articleVue);
            $articleVue->setArticle($this);
        }

        return $this;
    }

    /**
     * Retire une vue de cet article. Gère la relation bidirectionnelle avec ArticleVue.
     * @param ArticleVue $articleVue La vue à retirer
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function removeArticleVue(ArticleVue $articleVue): static
    {
        if ($this->articleVues->removeElement($articleVue)) {
            // Met à jour le côté possesseur de la relation
            if ($articleVue->getArticle() === $this) {
                $articleVue->setArticle(null);
            }
        }

        return $this;
    }

}
