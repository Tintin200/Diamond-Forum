<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260608160102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article_like (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, article_id INT NOT NULL, INDEX IDX_1C21C7B2A76ED395 (user_id), INDEX IDX_1C21C7B27294869C (article_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE article_vue (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, article_id INT NOT NULL, INDEX IDX_52B2F6EEA76ED395 (user_id), INDEX IDX_52B2F6EE7294869C (article_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE article_like ADD CONSTRAINT FK_1C21C7B2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE article_like ADD CONSTRAINT FK_1C21C7B27294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE article_vue ADD CONSTRAINT FK_52B2F6EEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE article_vue ADD CONSTRAINT FK_52B2F6EE7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE article ADD image VARCHAR(255) DEFAULT NULL, ADD author_id INT NOT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_23A0E66F675F31B ON article (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_like DROP FOREIGN KEY FK_1C21C7B2A76ED395');
        $this->addSql('ALTER TABLE article_like DROP FOREIGN KEY FK_1C21C7B27294869C');
        $this->addSql('ALTER TABLE article_vue DROP FOREIGN KEY FK_52B2F6EEA76ED395');
        $this->addSql('ALTER TABLE article_vue DROP FOREIGN KEY FK_52B2F6EE7294869C');
        $this->addSql('DROP TABLE article_like');
        $this->addSql('DROP TABLE article_vue');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66F675F31B');
        $this->addSql('DROP INDEX IDX_23A0E66F675F31B ON article');
        $this->addSql('ALTER TABLE article DROP image, DROP author_id');
    }
}
