<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221123120740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stadt ADD setting_skib_default_next_change LONGTEXT DEFAULT NULL, ADD settings_skib_show_set_start_date_on_change TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE stadt_translation ADD settings_skib_text_when_closed LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stadt DROP setting_skib_default_next_change, DROP settings_skib_show_set_start_date_on_change');
        $this->addSql('ALTER TABLE stadt_translation DROP settings_skib_text_when_closed');
    }
}
