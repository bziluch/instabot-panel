<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250923140914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added schedules';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE schedule (id INT AUTO_INCREMENT NOT NULL, ig_account_id INT NOT NULL, date DATETIME NOT NULL, amount INT NOT NULL, fulfilled INT NOT NULL, status SMALLINT NOT NULL, INDEX IDX_5A3811FB497F0D9D (ig_account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB497F0D9D FOREIGN KEY (ig_account_id) REFERENCES ig_account (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FB497F0D9D');
        $this->addSql('DROP TABLE schedule');
    }
}
