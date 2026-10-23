<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\EquipementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Equipement - Entité représentant un équipement de baseball.
 * Exposée via API Platform avec endpoints REST complets.
 * @ORM\Entity(repositoryClass=EquipementRepository::class)
 * @ApiResource
 */
#[ORM\Entity(repositoryClass: EquipementRepository::class)]
#[ApiResource]
class Equipement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $categorie = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?int $sport = null;

    /**
     * Retourne l'ID de l'équipement.
     *
     * @return int|null L'ID (null si non persiste)
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le nom de l'équipement.
     *
     * @return string|null Le nom
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * Définit le nom de l'équipement.
     *
     * @param string $nom Le nom
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Retourne la catégorie de l'équipement.
     * @return string|null La catégorie
     */
    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    /**
     * Définit la catégorie de l'équipement.
     *
     * @param string $categorie La catégorie
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Retourne la description de l'équipement.
     *
     * @return string|null La description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Définit la description de l'équipement.
     *
     * @param string $description La description
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Retourne l'URL de l'image.
     *
     * @return string|null L'URL de l'image (null si pas d'image)
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * Définit l'URL de l'image.
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
     * Retourne l'ID du sport. 1 = Baseball.
     * @return int|null L'ID du sport
     */
    public function getSport(): ?int
    {
        return $this->sport;
    }

    /**
     * Définit l'ID du sport.
     *
     * @param int $sport L'ID du sport (1 pour baseball)
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setSport(int $sport): static
    {
        $this->sport = $sport;

        return $this;
    }
}
