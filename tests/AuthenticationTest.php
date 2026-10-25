<?php

namespace App\Tests;

use App\Repository\UserRepository;
use App\Repository\ArticleRepository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * AuthenticationTest - Tests fonctionnels pour l'authentification et les autorisations.
 *
 * Rôle : Vérifie que le système d'authentification et d'autorisation fonctionne correctement.
 *
 * Tests inclus :
 * 1. testLoginAndAccessSecuredRoute() : Teste le flux complet de login et l'accès aux routes sécurisées
 * 2. testArticleAuthorization() : Teste les autorisations pour la modification des articles
 *
 * Utilise :
 * - WebTestCase de Symfony : Pour simuler des requêtes HTTP
 * - Les fixtures AppFixtures : Pour avoir des données de test en base
 * - Les utilisateurs : admin@diamond.com (ROLE_ADMIN) et aymerick@diamond.com (ROLE_USER)
 *
 * Dépendances :
 * - UserRepository : Pour récupérer les utilisateurs
 * - ArticleRepository : Pour récupérer les articles
 *
 * @package App\Tests
 */
class AuthenticationTest extends WebTestCase
{
    /**
     * Teste le flux complet d'authentification et d'accès aux routes sécurisées.
     *
     * Scénarios testés :
     * 1. Accès à une route sécurisée (/api/equipements) SANS token → 401 Unauthorized
     * 2. Tentative de login avec des identifiants INVALIDES → 401 Unauthorized
     * 3. Login avec des identifiants VALIDES (aymerick@diamond.com / password) → 200 OK
     *    - Vérifie que la réponse contient un token JWT
     * 4. Accès à une route sécurisée AVEC token → 200 OK
     *
     * Utilise l'utilisateur aymerick@diamond.com avec mot de passe 'password'.
     *
     * @return void
     */
    public function testLoginAndAccessSecuredRoute(): void
    {
        $client = static::createClient();

        // 1. Try to access secure endpoint without token
        $client->request('GET', '/api/equipements');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        // 2. Try to login with invalid credentials
        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'ACCEPT' => 'application/json'],
            json_encode([
                'email' => 'aymerick@diamond.com',
                'password' => 'wrong_password'
            ])
        );
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        // 3. Login with valid credentials
        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'ACCEPT' => 'application/json'],
            json_encode([
                'email' => 'aymerick@diamond.com',
                'password' => 'password'
            ])
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $token = $data['token'];

        // 4. Access secure endpoint with token
        $client->request(
            'GET',
            '/api/equipements',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Teste les autorisations pour la modification des articles.
     *
     * Scénarios testés :
     * 1. Accès non connecté à /articles/{id}/modifier → Redirection vers /login
     * 2. Connecté en tant qu'utilisateur normal (aymerick@diamond.com) :
     *    - Accès à SON PROPRE article → 200 OK
     *    - Accès à l'article d'un AUTRE → 403 Forbidden
     * 3. Connecté en tant qu'admin (admin@diamond.com) :
     *    - Accès à N'IMPORTE QUEL article → 200 OK
     *
     * Utilise :
     * - aymerick@diamond.com (ROLE_USER) : Utilisateur normal
     * - admin@diamond.com (ROLE_ADMIN) : Administrateur
     *
     * @return void
     */
    public function testArticleAuthorization(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        
        $userRepository = $container->get(UserRepository::class);
        $articleRepository = $container->get(ArticleRepository::class);

        // Find Aymerick and Admin
        $aymerick = $userRepository->findOneByEmail('aymerick@diamond.com');
        $admin = $userRepository->findOneByEmail('admin@diamond.com');
        
        // Skip test if users not found
        if (!$aymerick || !$admin) {
            $this->markTestSkipped('Test users not found in database');
        }

        // Find an article not owned by Aymerick
        $allArticles = $articleRepository->findAll();
        $otherArticle = null;
        $ownArticle = null;

        foreach ($allArticles as $art) {
            if ($art->getAuthor() !== null && $art->getAuthor()->getId() === $aymerick->getId()) {
                $ownArticle = $art;
            } elseif ($art->getAuthor() !== null) {
                $otherArticle = $art;
            }
        }

        // --- SCENARIO 1: NOT LOGGED IN ---
        if ($otherArticle) {
            $client->request('GET', sprintf('/articles/%d/modifier', $otherArticle->getId()));
            $this->assertTrue($client->getResponse()->isRedirect('/login'));
        }

        // --- SCENARIO 2: LOGGED IN AS AYMERICK ---
        $client->loginUser($aymerick);

        // Accessing other's article -> 403 Access Denied
        if ($otherArticle) {
            $client->request('GET', sprintf('/articles/%d/modifier', $otherArticle->getId()));
            $this->assertEquals(403, $client->getResponse()->getStatusCode());
        }

        // Accessing own article -> 200 OK
        if ($ownArticle) {
            $client->request('GET', sprintf('/articles/%d/modifier', $ownArticle->getId()));
            $this->assertEquals(200, $client->getResponse()->getStatusCode());
        }

        // --- SCENARIO 3: LOGGED IN AS ADMIN ---
        $client->loginUser($admin);

        // Accessing any article -> 200 OK
        if ($otherArticle) {
            $client->request('GET', sprintf('/articles/%d/modifier', $otherArticle->getId()));
            if ($otherArticle) {
            $client->request('GET', sprintf('/articles/%d/modifier', $otherArticle->getId()));

            $response = $client->getResponse();
            $this->assertEquals(
                200,
                $response->getStatusCode(),
                substr(trim(preg_replace('/\s+/', ' ', strip_tags($response->getContent()))), 0, 1500)
            );
        }
        }
    }
}

