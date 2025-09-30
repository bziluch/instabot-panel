<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250930131634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_request (id INT AUTO_INCREMENT NOT NULL, account_id INT DEFAULT NULL, type SMALLINT NOT NULL, directory SMALLINT NOT NULL, message LONGTEXT NOT NULL, response LONGTEXT DEFAULT NULL, status SMALLINT NOT NULL, create_date DATETIME NOT NULL, response_date DATETIME DEFAULT NULL, INDEX IDX_D6CA0FD29B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_request ADD CONSTRAINT FK_D6CA0FD29B6B5FBA FOREIGN KEY (account_id) REFERENCES ig_account (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_request DROP FOREIGN KEY FK_D6CA0FD29B6B5FBA');
        $this->addSql('DROP TABLE app_request');
    }
}
