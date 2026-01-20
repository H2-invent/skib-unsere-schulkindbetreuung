<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120113107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE late_registration (id INT AUTO_INCREMENT NOT NULL, stadt_id INT NOT NULL, schuljahr_id INT NOT NULL, email VARCHAR(255) NOT NULL, uri VARCHAR(512) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', token BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_BE20FF38D9D9BB9 (stadt_id), INDEX IDX_BE20FF336D9555 (schuljahr_id), INDEX IDX_BE20FF3E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE late_registration ADD CONSTRAINT FK_BE20FF38D9D9BB9 FOREIGN KEY (stadt_id) REFERENCES stadt (id)');
        $this->addSql('ALTER TABLE late_registration ADD CONSTRAINT FK_BE20FF336D9555 FOREIGN KEY (schuljahr_id) REFERENCES active (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE late_registration DROP FOREIGN KEY FK_BE20FF38D9D9BB9');
        $this->addSql('ALTER TABLE late_registration DROP FOREIGN KEY FK_BE20FF336D9555');
        $this->addSql('DROP TABLE late_registration');
    }
}
