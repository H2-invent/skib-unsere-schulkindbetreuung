<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240415110420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kind CHANGE tracing tracing VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_tracing ON kind (tracing)');
        $this->addSql('ALTER TABLE stammdaten CHANGE tracing tracing VARCHAR(255) DEFAULT NULL, CHANGE tracing_of_last_year tracing_of_last_year VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_tracing ON stammdaten (tracing)');
        $this->addSql('CREATE INDEX idx_tracing_of_last_year ON stammdaten (tracing_of_last_year)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_tracing ON kind');
        $this->addSql('ALTER TABLE kind CHANGE tracing tracing LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX idx_tracing ON stammdaten');
        $this->addSql('DROP INDEX idx_tracing_of_last_year ON stammdaten');
        $this->addSql('ALTER TABLE stammdaten CHANGE tracing tracing LONGTEXT DEFAULT NULL, CHANGE tracing_of_last_year tracing_of_last_year LONGTEXT DEFAULT NULL');
    }
}
