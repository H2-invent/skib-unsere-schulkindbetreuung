<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241119083207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE kinder__warteliste (kind_id INT NOT NULL, zeitblock_id INT NOT NULL, INDEX IDX_29C12CB30602CA9 (kind_id), INDEX IDX_29C12CB98597586 (zeitblock_id), PRIMARY KEY(kind_id, zeitblock_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kinder__warteliste ADD CONSTRAINT FK_29C12CB30602CA9 FOREIGN KEY (kind_id) REFERENCES kind (id)');
        $this->addSql('ALTER TABLE kinder__warteliste ADD CONSTRAINT FK_29C12CB98597586 FOREIGN KEY (zeitblock_id) REFERENCES zeitblock (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kinder__warteliste DROP FOREIGN KEY FK_29C12CB30602CA9');
        $this->addSql('ALTER TABLE kinder__warteliste DROP FOREIGN KEY FK_29C12CB98597586');
        $this->addSql('DROP TABLE kinder__warteliste');
    }
}
