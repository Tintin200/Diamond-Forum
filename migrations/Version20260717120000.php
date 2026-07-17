<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Schéma initial pour PostgreSQL (Render).
 *
 * Remplace les 3 migrations MySQL d'origine (Version20260606132924,
 * Version20260606134513, Version20260608160102), qui utilisaient une
 * syntaxe spécifique à MySQL (AUTO_INCREMENT, LONGTEXT, ENGINE...) non
 * compatible avec PostgreSQL. Cette migration reflète l'état final des
 * entités Doctrine actuelles (y compris la colonne `article.tags`, qui
 * existait sur l'entité mais n'était pas encore dans une migration).
 */
final class Version20260717120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Schéma initial PostgreSQL (article, user, contact, equipement, article_like, article_vue, messenger_messages)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');

        $this->addSql('CREATE TABLE article (id SERIAL NOT NULL, author_id INT NOT NULL, sujet VARCHAR(180) NOT NULL, contenu TEXT NOT NULL, auteur VARCHAR(150) NOT NULL, image VARCHAR(255) DEFAULT NULL, tags VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_23A0E66F675F31B ON article (author_id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE contact (id SERIAL NOT NULL, nom VARCHAR(30) NOT NULL, sujet VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL, message TEXT NOT NULL, PRIMARY KEY(id))');

        $this->addSql('CREATE TABLE equipement (id SERIAL NOT NULL, nom VARCHAR(150) NOT NULL, categorie VARCHAR(50) NOT NULL, description TEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, sport INT NOT NULL, PRIMARY KEY(id))');

        $this->addSql('CREATE TABLE article_like (id SERIAL NOT NULL, user_id INT NOT NULL, article_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1C21C7B2A76ED395 ON article_like (user_id)');
        $this->addSql('CREATE INDEX IDX_1C21C7B27294869C ON article_like (article_id)');
        $this->addSql('ALTER TABLE article_like ADD CONSTRAINT FK_1C21C7B2A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article_like ADD CONSTRAINT FK_1C21C7B27294869C FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE article_vue (id SERIAL NOT NULL, user_id INT NOT NULL, article_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_52B2F6EEA76ED395 ON article_vue (user_id)');
        $this->addSql('CREATE INDEX IDX_52B2F6EE7294869C ON article_vue (article_id)');
        $this->addSql('ALTER TABLE article_vue ADD CONSTRAINT FK_52B2F6EEA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article_vue ADD CONSTRAINT FK_52B2F6EE7294869C FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E66F675F31B');
        $this->addSql('ALTER TABLE article_like DROP CONSTRAINT FK_1C21C7B2A76ED395');
        $this->addSql('ALTER TABLE article_like DROP CONSTRAINT FK_1C21C7B27294869C');
        $this->addSql('ALTER TABLE article_vue DROP CONSTRAINT FK_52B2F6EEA76ED395');
        $this->addSql('ALTER TABLE article_vue DROP CONSTRAINT FK_52B2F6EE7294869C');

        $this->addSql('DROP TABLE article_like');
        $this->addSql('DROP TABLE article_vue');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE equipement');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE "user"');
    }
}
