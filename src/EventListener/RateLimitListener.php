<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * RateLimitListener - Applique le rate limiting sur les requêtes API.
 * 
 * Protège contre :
 * - Les attaques par force brute (brute force)
 * - Les attaques DDoS
 * - L'abus de l'API
 */
final class RateLimitListener
{
    /**
     * @param RateLimiterFactoryInterface $apiLimiter Factory pour le rate limiter de l'API
     * @param RateLimiterFactoryInterface $authLimiter Factory pour le rate limiter d'authentification
     * @param RateLimiterFactoryInterface $sensitivePagesLimiter Factory pour les pages sensibles
     */
    public function __construct(
        private RateLimiterFactoryInterface $apiLimiter,
        private RateLimiterFactoryInterface $authLimiter,
        private RateLimiterFactoryInterface $sensitivePagesLimiter
    ) {}

    /**
     * Vérifie et applique le rate limiting sur la requête entrante.
     * 
     * @param RequestEvent $event L'événement de requête HTTP
     */
    #[AsEventListener(event: KernelEvents::REQUEST, priority: 200)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Appliquer le rate limiting pour les endpoints API
        if (str_starts_with($path, '/api')) {
            $limiter = $this->apiLimiter->create($request->getClientIp());
            
            if (false === $limiter->consume()) {
                $retryAfter = $limiter->getRetryAfter();
                $limit = $limiter->getLimit();
                $remaining = $limiter->getRemainingAttempts();
                
                $response = new TooManyRequestsHttpException($retryAfter);
                $response->headers->set('X-RateLimit-Limit', $limit);
                $response->headers->set('X-RateLimit-Remaining', max(0, $remaining));
                $response->headers->set('Retry-After', $retryAfter);
                
                throw $response;
            }
            
            // Ajouter les en-têtes de rate limit aux réponses API
            $response = $event->getResponse();
            if ($response) {
                $limiter = $this->apiLimiter->create($request->getClientIp());
                $response->headers->set('X-RateLimit-Limit', $limiter->getLimit());
                $response->headers->set('X-RateLimit-Remaining', $limiter->getRemainingAttempts());
            }
        }

        // Appliquer le rate limiting pour les endpoints d'authentification
        if (str_starts_with($path, '/login') || str_starts_with($path, '/register') || str_starts_with($path, '/api/login')) {
            $limiter = $this->authLimiter->create($request->getClientIp());
            
            if (false === $limiter->consume()) {
                $retryAfter = $limiter->getRetryAfter();
                $limit = $limiter->getLimit();
                $remaining = $limiter->getRemainingAttempts();
                
                $response = new TooManyRequestsHttpException($retryAfter);
                $response->headers->set('X-RateLimit-Limit', $limit);
                $response->headers->set('X-RateLimit-Remaining', max(0, $remaining));
                $response->headers->set('Retry-After', $retryAfter);
                
                throw $response;
            }
        }
    }
}
