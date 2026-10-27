<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * AppCustomAuthenticator - Authentificateur personnalisé pour le formulaire de login.
 * Étend AbstractLoginFormAuthenticator avec UserBadge, PasswordCredentials, CsrfTokenBadge, RememberMeBadge.
 */
class AppCustomAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    /**
     * Route vers la page de login.
     */
    public const LOGIN_ROUTE = 'app_login';

    /**
     * Constructeur - Injecte le générateur d'URL.
     *
     * @param UrlGeneratorInterface $urlGenerator Service pour générer les URLs
     */
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * Authentifie l'utilisateur via le formulaire de login.
     * Crée un Passport avec UserBadge, PasswordCredentials, CsrfTokenBadge, RememberMeBadge.
     * @param Request $request Requête HTTP contenant les credentials
     * @return Passport Objet Passport contenant les badges d'authentification
     */
    public function authenticate(Request $request): Passport
    {
        $email = $request->getPayload()->getString('email');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    /**
     * Gère la redirection après une authentification réussie.
     * Redirige vers la page demandée (target path) ou l'accueil.
     * @param Request $request Requête originale
     * @param TokenInterface $token Token d'authentification
     * @param string $firewallName Nom du firewall
     * @return RedirectResponse Redirection vers la page demandée ou l'accueil
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    /**
     * Retourne l'URL de la page de login.
     * @param Request $request Requête (non utilisée ici)
     * @return string URL de la page de login
     */
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
