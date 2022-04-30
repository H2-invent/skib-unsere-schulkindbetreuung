<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220430103509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE geschwister (id INT AUTO_INCREMENT NOT NULL, stammdaten_id INT NOT NULL, vorname LONGTEXT DEFAULT NULL, nachname LONGTEXT DEFAULT NULL, geburtsdatum DATETIME DEFAULT NULL, INDEX IDX_443CFE0CD918E60 (stammdaten_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geschwister_file (geschwister_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_C1F089196C14CE3F (geschwister_id), INDEX IDX_C1F0891993CB796C (file_id), PRIMARY KEY(geschwister_id, file_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE geschwister ADD CONSTRAINT FK_443CFE0CD918E60 FOREIGN KEY (stammdaten_id) REFERENCES stammdaten (id)');
        $this->addSql('ALTER TABLE geschwister_file ADD CONSTRAINT FK_C1F089196C14CE3F FOREIGN KEY (geschwister_id) REFERENCES geschwister (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE geschwister_file ADD CONSTRAINT FK_C1F0891993CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE geschwister_file DROP FOREIGN KEY FK_C1F089196C14CE3F');
        $this->addSql('DROP TABLE geschwister');
        $this->addSql('DROP TABLE geschwister_file');
    }
}
