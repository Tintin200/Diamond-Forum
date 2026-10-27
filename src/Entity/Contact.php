<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contact - Entité représentant un message de contact.
 * Stocke les messages soumis via le formulaire de contact du site.
 * @ORM\Entity(repositoryClass=ContactRepository::class)
 */
#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $sujet = null;

    #[ORM\Column(length: 100)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    /**
     * Retourne l'ID du message de contact.
     *
     * @return int|null L'ID (null si non persiste)
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le nom de l'expéditeur.
     *
     * @return string|null Le nom de l'expéditeur
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * Définit le nom de l'expéditeur.
     *
     * @param string $nom Le nom de l'expéditeur
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Retourne le sujet du message.
     *
     * @return string|null Le sujet
     */
    public function getSujet(): ?string
    {
        return $this->sujet;
    }

    /**
     * Définit le sujet du message.
     *
     * @param string $sujet Le sujet
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setSujet(string $sujet): static
    {
        $this->sujet = $sujet;

        return $this;
    }

    /**
     * Retourne l'adresse email de l'expéditeur.
     *
     * @return string|null L'adresse email
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Définit l'adresse email de l'expéditeur.
     *
     * @param string $email L'adresse email
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Retourne le contenu du message.
     *
     * @return string|null Le message
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Définit le contenu du message.
     *
     * @param string $message Le message
     * @return self Retourne l'instance courante pour le chaînage
     */
    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
