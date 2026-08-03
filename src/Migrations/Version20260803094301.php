<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260803094301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'SKIB-85: Versand-Flag und PDF-Template für den Gebührenbescheid des Ferienprogramms';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stadt ADD settings_ferien_send_gebuehrenbescheid TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE stadt_translation ADD pdftemplate_gebuehrenbescheid_ferien LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stadt DROP settings_ferien_send_gebuehrenbescheid');
        $this->addSql('ALTER TABLE stadt_translation DROP pdftemplate_gebuehrenbescheid_ferien');
    }
}
