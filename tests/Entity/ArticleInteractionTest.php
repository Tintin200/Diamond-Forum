<?php

namespace App\Tests\Entity;

use App\Entity\Article;
use App\Entity\ArticleLike;
use App\Entity\ArticleVue;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ArticleInteractionTest extends TestCase
{
    public function testArticleLikeGettersSetters(): void
    {
        $like = new ArticleLike();
        $user = new User();
        $article = new Article();
        $createdAt = new \DateTime();

        $like->setUser($user);
        $this->assertSame($user, $like->getUser());

        $like->setArticle($article);
        $this->assertSame($article, $like->getArticle());

        $like->setCreatedAt($createdAt);
        $this->assertSame($createdAt, $like->getCreatedAt());
    }

    public function testArticleVueGettersSetters(): void
    {
        $vue = new ArticleVue();
        $user = new User();
        $article = new Article();

        $vue->setUser($user);
        $this->assertSame($user, $vue->getUser());

        $vue->setArticle($article);
        $this->assertSame($article, $vue->getArticle());
    }
}
