<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220418093336 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stadt ADD setting_kinderim_kiga TINYINT(1) DEFAULT NULL, ADD setting_gehaltsklassen TINYINT(1) DEFAULT NULL, ADD setting_gehaltsklassen_required TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE stadt_translation ADD setting_gehaltsklassen_help LONGTEXT DEFAULT NULL, ADD setting_kinderim_kiga_help LONGTEXT DEFAULT NULL, ADD settings_anzahl_kindergeldempfanger_help LONGTEXT DEFAULT NULL, ADD settings_soziel_hilfe_empfanger_help LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stadt DROP setting_kinderim_kiga, DROP setting_gehaltsklassen, DROP setting_gehaltsklassen_required');
        $this->addSql('ALTER TABLE stadt_translation DROP setting_gehaltsklassen_help, DROP setting_kinderim_kiga_help, DROP settings_anzahl_kindergeldempfanger_help, DROP settings_soziel_hilfe_empfanger_help');
    }
}
