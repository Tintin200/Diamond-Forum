<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppCustomAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * RegistrationController - Gère le processus d'inscription des nouveaux utilisateurs.
 * Affichage du formulaire, validation, hachage du mot de passe, persistance et connexion automatique.
 */
class RegistrationController extends AbstractController
{
    /**
     * Traite l'inscription d'un nouvel utilisateur.
     * Hache le mot de passe, persiste l'utilisateur et le connecte automatiquement.
     * @param Request $request Requête HTTP contenant les données du formulaire
     * @param UserPasswordHasherInterface $userPasswordHasher Service pour hacher les mots de passe
     * @param Security $security Service pour connecter l'utilisateur après inscription
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités pour persister l'utilisateur
     * @return Response Redirection vers la page d'accueil ou rendu du formulaire
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $security->login($user, AppCustomAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
