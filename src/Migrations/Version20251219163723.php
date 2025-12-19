<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251219163723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE stammdaten_file (stammdaten_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_8A0F2897CD918E60 (stammdaten_id), INDEX IDX_8A0F289793CB796C (file_id), PRIMARY KEY(stammdaten_id, file_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE stammdaten_file ADD CONSTRAINT FK_8A0F2897CD918E60 FOREIGN KEY (stammdaten_id) REFERENCES stammdaten (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stammdaten_file ADD CONSTRAINT FK_8A0F289793CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stadt ADD settings_dokument_upload_text VARCHAR(1024) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stammdaten_file DROP FOREIGN KEY FK_8A0F2897CD918E60');
        $this->addSql('ALTER TABLE stammdaten_file DROP FOREIGN KEY FK_8A0F289793CB796C');
        $this->addSql('DROP TABLE stammdaten_file');
        $this->addSql('ALTER TABLE stadt DROP settings_dokument_upload_text');
    }
}
