<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260409103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add city setting to enable parent sick dashboard feature';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stadt ADD settings_skib_enable_parent_sick_dashboard TINYINT(1) DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stadt DROP settings_skib_enable_parent_sick_dashboard');
    }
}

