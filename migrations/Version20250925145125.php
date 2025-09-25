<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250925145125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add accosted accounts';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE accosted_accounts (id INT AUTO_INCREMENT NOT NULL, schedule_id INT NOT NULL, name VARCHAR(30) NOT NULL, status SMALLINT NOT NULL, INDEX IDX_7FD3BB13A40BC2D5 (schedule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE accosted_accounts ADD CONSTRAINT FK_7FD3BB13A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE accosted_accounts DROP FOREIGN KEY FK_7FD3BB13A40BC2D5');
        $this->addSql('DROP TABLE accosted_accounts');
    }
}
