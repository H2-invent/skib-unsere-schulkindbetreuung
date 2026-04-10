<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260408143849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE zeitblock_vorganger_silent (zeitblock_source INT NOT NULL, zeitblock_target INT NOT NULL, INDEX IDX_2473A71B83E030E4 (zeitblock_source), INDEX IDX_2473A71B9A05606B (zeitblock_target), PRIMARY KEY(zeitblock_source, zeitblock_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE zeitblock_vorganger_silent ADD CONSTRAINT FK_2473A71B83E030E4 FOREIGN KEY (zeitblock_source) REFERENCES zeitblock (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE zeitblock_vorganger_silent ADD CONSTRAINT FK_2473A71B9A05606B FOREIGN KEY (zeitblock_target) REFERENCES zeitblock (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE zeitblock_vorganger_silent DROP FOREIGN KEY FK_2473A71B83E030E4');
        $this->addSql('ALTER TABLE zeitblock_vorganger_silent DROP FOREIGN KEY FK_2473A71B9A05606B');
        $this->addSql('DROP TABLE zeitblock_vorganger_silent');
    }
}
