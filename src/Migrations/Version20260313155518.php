<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260313155518 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kind ADD chronical_deseas LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE stadt ADD skip_setting_show_chronical_deseas TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE stadt_translation ADD settings_chronical_deses_help LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kind DROP chronical_deseas');
        $this->addSql('ALTER TABLE stadt DROP skip_setting_show_chronical_deseas');
        $this->addSql('ALTER TABLE stadt_translation DROP settings_chronical_deses_help');
    }
}
