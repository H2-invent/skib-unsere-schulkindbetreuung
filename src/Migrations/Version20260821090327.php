<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260821090327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE late_registration ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE late_registration ADD CONSTRAINT FK_BE20FF3A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('CREATE INDEX IDX_BE20FF3A76ED395 ON late_registration (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE late_registration DROP FOREIGN KEY FK_BE20FF3A76ED395');
        $this->addSql('DROP INDEX IDX_BE20FF3A76ED395 ON late_registration');
        $this->addSql('ALTER TABLE late_registration DROP user_id');
    }
}
