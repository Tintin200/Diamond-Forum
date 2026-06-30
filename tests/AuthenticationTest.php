<?php

namespace App\Tests;

use App\Repository\UserRepository;
use App\Repository\ArticleRepository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthenticationTest extends WebTestCase
{
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
            ['CONTENT_TYPE' => 'application/json'],
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
            ['CONTENT_TYPE' => 'application/json'],
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

    public function testArticleAuthorization(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        
        $userRepository = $container->get(UserRepository::class);
        $articleRepository = $container->get(ArticleRepository::class);

        // Find Aymerick and Admin
        $aymerick = $userRepository->findOneByEmail('aymerick@diamond.com');
        $admin = $userRepository->findOneByEmail('admin@diamond.com');

        // Find an article not owned by Aymerick
        $allArticles = $articleRepository->findAll();
        $otherArticle = null;
        $ownArticle = null;

        foreach ($allArticles as $art) {
            if ($art->getAuthor()->getId() !== $aymerick->getId()) {
                $otherArticle = $art;
            } else {
                $ownArticle = $art;
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
            $this->assertEquals(200, $client->getResponse()->getStatusCode());
        }
    }
}

