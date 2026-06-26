<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260626120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add per-city custom CSS for frontend pages';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stadt ADD custom_css LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stadt DROP custom_css');
    }
}
