<?php

namespace App\Tests\Entity;

use App\Entity\Article;
use App\Entity\User;
use App\Entity\ArticleLike;
use App\Entity\ArticleVue;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function testGettersSetters(): void
    {
        $article = new Article();

        $article->setSujet('Baseball Match Today');
        $this->assertEquals('Baseball Match Today', $article->getSujet());

        $article->setContenu('A beautiful match was played today.');
        $this->assertEquals('A beautiful match was played today.', $article->getContenu());

        $article->setAuteur('John Doe');
        $this->assertEquals('John Doe', $article->getAuteur());

        $article->setImage('match.jpg');
        $this->assertEquals('match.jpg', $article->getImage());

        $article->setTags('baseball,sports');
        $this->assertEquals('baseball,sports', $article->getTags());

        $author = new User();
        $article->setAuthor($author);
        $this->assertSame($author, $article->getAuthor());
    }

    public function testArticleLikesRelation(): void
    {
        $article = new Article();
        $like = new ArticleLike();

        $this->assertCount(0, $article->getArticleLikes());

        $article->addArticleLike($like);
        $this->assertCount(1, $article->getArticleLikes());
        $this->assertTrue($article->getArticleLikes()->contains($like));
        $this->assertSame($article, $like->getArticle());

        $article->removeArticleLike($like);
        $this->assertCount(0, $article->getArticleLikes());
        $this->assertFalse($article->getArticleLikes()->contains($like));
        $this->assertNull($like->getArticle());
    }

    public function testArticleVuesRelation(): void
    {
        $article = new Article();
        $vue = new ArticleVue();

        $this->assertCount(0, $article->getArticleVues());

        $article->addArticleVue($vue);
        $this->assertCount(1, $article->getArticleVues());
        $this->assertTrue($article->getArticleVues()->contains($vue));
        $this->assertSame($article, $vue->getArticle());

        $article->removeArticleVue($vue);
        $this->assertCount(0, $article->getArticleVues());
        $this->assertFalse($article->getArticleVues()->contains($vue));
        $this->assertNull($vue->getArticle());
    }
}
