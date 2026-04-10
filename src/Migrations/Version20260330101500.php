<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260330101500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a flag to disable direct booking while still allowing dependency booking.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE zeitblock ADD direktbuchung_deaktiviert TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE zeitblock DROP direktbuchung_deaktiviert');
    }
}
