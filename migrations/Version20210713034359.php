<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210713034359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE exercice (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', muscle_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:ulid)\', slug VARCHAR(100) NOT NULL, name VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E418C74D354FDBB4 (muscle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE muscle (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', muscle_owner_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', target VARCHAR(100) NOT NULL, upper_or_lower_body VARCHAR(6) NOT NULL, slug VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F31119EF8428BC6E (muscle_owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE repetition (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', exercice_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:ulid)\', number INT NOT NULL, weight DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9DB9AD5289D40298 (exercice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', gender VARCHAR(10) NOT NULL, slug VARCHAR(100) NOT NULL, alias VARCHAR(100) NOT NULL, country VARCHAR(100) DEFAULT NULL, last_connection DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_blocked_by_attempt TINYINT(1) NOT NULL, is_blocked_by_admin TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE exercice ADD CONSTRAINT FK_E418C74D354FDBB4 FOREIGN KEY (muscle_id) REFERENCES muscle (id)');
        $this->addSql('ALTER TABLE muscle ADD CONSTRAINT FK_F31119EF8428BC6E FOREIGN KEY (muscle_owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE repetition ADD CONSTRAINT FK_9DB9AD5289D40298 FOREIGN KEY (exercice_id) REFERENCES exercice (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE repetition DROP FOREIGN KEY FK_9DB9AD5289D40298');
        $this->addSql('ALTER TABLE exercice DROP FOREIGN KEY FK_E418C74D354FDBB4');
        $this->addSql('ALTER TABLE muscle DROP FOREIGN KEY FK_F31119EF8428BC6E');
        $this->addSql('DROP TABLE exercice');
        $this->addSql('DROP TABLE muscle');
        $this->addSql('DROP TABLE repetition');
        $this->addSql('DROP TABLE `user`');
    }
}
