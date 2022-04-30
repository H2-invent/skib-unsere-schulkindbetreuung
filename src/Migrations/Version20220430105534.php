<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220430105534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stadt ADD settings_eingabe_der_geschwister TINYINT(1) DEFAULT NULL, ADD settingsweitere_personenberechtigte TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE stadt_translation ADD settings_eingabe_der_geschwister_help LONGTEXT DEFAULT NULL, ADD settingsweitere_personenberechtigte_help LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stadt DROP settings_eingabe_der_geschwister, DROP settingsweitere_personenberechtigte');
        $this->addSql('ALTER TABLE stadt_translation DROP settings_eingabe_der_geschwister_help, DROP settingsweitere_personenberechtigte_help');
    }
}
