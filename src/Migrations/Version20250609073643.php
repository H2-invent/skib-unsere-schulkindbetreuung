<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250609073643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stadt_translation ADD emailtemplate_anmeldung LONGTEXT DEFAULT NULL, ADD emailtemplate_buchung LONGTEXT DEFAULT NULL, ADD emailtemplate_abmeldung LONGTEXT DEFAULT NULL, ADD emailtemplate_stammdaten_edit LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stadt_translation DROP emailtemplate_anmeldung, DROP emailtemplate_buchung, DROP emailtemplate_abmeldung, DROP emailtemplate_stammdaten_edit');
    }
}
