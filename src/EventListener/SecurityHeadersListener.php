<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * SecurityHeadersListener - Ajoute les en-têtes de sécurité HTTP à toutes les réponses.
 * 
 * Protège contre :
 * - XSS (Content Security Policy)
 * - Clickjacking (X-Frame-Options, CSP frame-ancestors)
 * - MIME-sniffing (X-Content-Type-Options)
 * - XSS via navigateur (X-XSS-Protection)
 * - Fuite d'informations (Referrer-Policy)
 * - Force HTTPS (HSTS)
 */
final class SecurityHeadersListener
{
    /**
     * Ajoute les en-têtes de sécurité à la réponse.
     * 
     * @param ResponseEvent $event L'événement de réponse HTTP
     */
    #[AsEventListener(event: KernelEvents::RESPONSE, priority: 100)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();

        // Content Security Policy (CSP)
        // Protège contre les attaques XSS en restreignant les sources autorisées
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https: http:; " .
               "style-src 'self' 'unsafe-inline' https: http:; " .
               "img-src 'self' data: https: http:; " .
               "font-src 'self' https: http:; " .
               "connect-src 'self' https: http:; " .
               "frame-src 'none'; " .
               "object-src 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self'; " .
               "frame-ancestors 'none'";
        
        $response->headers->set('Content-Security-Policy', $csp);

        // HTTP Strict Transport Security (HSTS)
        // Force HTTPS pour toutes les connexions pendant 1 an
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // X-Content-Type-Options
        // Empêche le navigateur de deviner le type MIME
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-Frame-Options
        // Protège contre le clickjacking (redondant avec CSP frame-ancestors)
        $response->headers->set('X-Frame-Options', 'DENY');

        // X-XSS-Protection
        // Active la protection XSS du navigateur
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy
        // Contrôle quelles informations de référent sont envoyées
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy
        // Contrôle quelles fonctionnalités du navigateur peuvent être utilisées
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), camera=(), microphone=(), payment=(), usb=()'
        );

        // Cache-Control pour les pages sensibles
        // Empêche la mise en cache des pages avec des données sensibles
        if ($this->isSensitiveRoute($event->getRequest()->getPathInfo())) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }
    }

    /**
     * Vérifie si la route est sensible (login, register, admin, etc.).
     * 
     * @param string $path Le chemin de la requête
     * @return bool Vrai si la route est sensible
     */
    private function isSensitiveRoute(string $path): bool
    {
        $sensitivePaths = [
            '/login',
            '/register',
            '/logout',
            '/admin',
            '/api/login',
            '/api/logout',
            '/api/register',
        ];

        foreach ($sensitivePaths as $sensitivePath) {
            if (str_starts_with($path, $sensitivePath)) {
                return true;
            }
        }

        return false;
    }
}
