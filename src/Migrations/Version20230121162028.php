<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230121162028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE zeitblock_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, extra_text LONGTEXT DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_7CF532E12C2AC5D3 (translatable_id), UNIQUE INDEX zeitblock_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE zeitblock_translation ADD CONSTRAINT FK_7CF532E12C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES zeitblock (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE zeitblock ADD clone_of_id INT DEFAULT NULL, ADD hide_price TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE zeitblock ADD CONSTRAINT FK_D02BD5E497A8D4D6 FOREIGN KEY (clone_of_id) REFERENCES zeitblock (id)');
        $this->addSql('CREATE INDEX IDX_D02BD5E497A8D4D6 ON zeitblock (clone_of_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE zeitblock_translation');
        $this->addSql('ALTER TABLE zeitblock DROP FOREIGN KEY FK_D02BD5E497A8D4D6');
        $this->addSql('DROP INDEX IDX_D02BD5E497A8D4D6 ON zeitblock');
        $this->addSql('ALTER TABLE zeitblock DROP clone_of_id, DROP hide_price');
    }
}
