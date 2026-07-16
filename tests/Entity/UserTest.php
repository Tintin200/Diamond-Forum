<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\ArticleLike;
use App\Entity\ArticleVue;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGettersSetters(): void
    {
        $user = new User();

        $user->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('test@example.com', $user->getUserIdentifier());

        $user->setPassword('my_password');
        $this->assertEquals('my_password', $user->getPassword());
    }

    public function testRoles(): void
    {
        $user = new User();

        // Default roles should always include ROLE_USER
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertCount(1, $user->getRoles());

        // Custom roles should be merged and include ROLE_USER
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertCount(2, $user->getRoles());

        // Ensure roles are unique
        $user->setRoles(['ROLE_ADMIN', 'ROLE_ADMIN']);
        $roles = $user->getRoles();
        $this->assertCount(2, $roles); // ROLE_ADMIN and ROLE_USER
    }

    public function testArticlesRelation(): void
    {
        $user = new User();
        $article = new Article();

        $this->assertCount(0, $user->getArticles());

        $user->addArticle($article);
        $this->assertCount(1, $user->getArticles());
        $this->assertTrue($user->getArticles()->contains($article));
        $this->assertSame($user, $article->getAuthor());

        $user->removeArticle($article);
        $this->assertCount(0, $user->getArticles());
        $this->assertFalse($user->getArticles()->contains($article));
        $this->assertNull($article->getAuthor());
    }

    public function testArticleLikesRelation(): void
    {
        $user = new User();
        $like = new ArticleLike();

        $this->assertCount(0, $user->getArticleLikes());

        $user->addArticleLike($like);
        $this->assertCount(1, $user->getArticleLikes());
        $this->assertTrue($user->getArticleLikes()->contains($like));
        $this->assertSame($user, $like->getUser());

        $user->removeArticleLike($like);
        $this->assertCount(0, $user->getArticleLikes());
        $this->assertFalse($user->getArticleLikes()->contains($like));
        $this->assertNull($like->getUser());
    }

    public function testArticleVuesRelation(): void
    {
        $user = new User();
        $vue = new ArticleVue();

        $this->assertCount(0, $user->getArticleVues());

        $user->addArticleVue($vue);
        $this->assertCount(1, $user->getArticleVues());
        $this->assertTrue($user->getArticleVues()->contains($vue));
        $this->assertSame($user, $vue->getUser());

        $user->removeArticleVue($vue);
        $this->assertCount(0, $user->getArticleVues());
        $this->assertFalse($user->getArticleVues()->contains($vue));
        $this->assertNull($vue->getUser());
    }

    public function testSerialize(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('secret');

        $serialized = serialize($user);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(User::class, $unserialized);
        $this->assertEquals('test@example.com', $unserialized->getEmail());
        // Password hash check
        $this->assertEquals(hash('crc32c', 'secret'), $unserialized->getPassword());
    }
}
