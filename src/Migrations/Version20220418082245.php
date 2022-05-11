<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220418082245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stadt ADD settings_anzahl_kindergeldempfanger TINYINT(1) NOT NULL, ADD settings_soziel_hilfe_empfanger TINYINT(1) NOT NULL, ADD settings_anzahl_kindergeldempfanger_required TINYINT(1) NOT NULL, ADD settings_soziel_hilfe_empfanger_required TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE stammdaten ADD anzahl_kindergeldempfanger INT DEFAULT NULL, ADD sozialhilfe_empanger TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stadt DROP settings_anzahl_kindergeldempfanger, DROP settings_soziel_hilfe_empfanger, DROP settings_anzahl_kindergeldempfanger_required, DROP settings_soziel_hilfe_empfanger_required');
        $this->addSql('ALTER TABLE stammdaten DROP anzahl_kindergeldempfanger, DROP sozialhilfe_empanger');
    }
}
