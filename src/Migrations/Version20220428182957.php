<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220428182957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE personenberechtigter (id INT AUTO_INCREMENT NOT NULL, stammdaten_id INT NOT NULL, vorname LONGTEXT NOT NULL, nachname LONGTEXT NOT NULL, strasse LONGTEXT NOT NULL, adresszusatz LONGTEXT DEFAULT NULL, plz LONGTEXT NOT NULL, stadt LONGTEXT NOT NULL, phone LONGTEXT DEFAULT NULL, email LONGTEXT DEFAULT NULL, notfallkontakt LONGTEXT DEFAULT NULL, INDEX IDX_3A881BD8CD918E60 (stammdaten_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE personenberechtigter ADD CONSTRAINT FK_3A881BD8CD918E60 FOREIGN KEY (stammdaten_id) REFERENCES stammdaten (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE personenberechtigter');
    }
}
