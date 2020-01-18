<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190912081324 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE abwesend CHANGE zeitblock_id zeitblock_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE active CHANGE zeitblock_id zeitblock_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE organisation CHANGE stadt_id stadt_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE schule CHANGE stadt_id stadt_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stammdaten CHANGE einkommen einkommen INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fos_user CHANGE salt salt VARCHAR(255) DEFAULT NULL, CHANGE last_login last_login DATETIME DEFAULT NULL, CHANGE confirmation_token confirmation_token VARCHAR(180) DEFAULT NULL, CHANGE password_requested_at password_requested_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE zeitblock CHANGE schule_id schule_id INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE abwesend CHANGE zeitblock_id zeitblock_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE active CHANGE zeitblock_id zeitblock_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fos_user CHANGE salt salt VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE last_login last_login DATETIME DEFAULT \'NULL\', CHANGE confirmation_token confirmation_token VARCHAR(180) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE password_requested_at password_requested_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE organisation CHANGE stadt_id stadt_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE schule CHANGE stadt_id stadt_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stammdaten CHANGE einkommen einkommen INT DEFAULT NULL');
        $this->addSql('ALTER TABLE zeitblock CHANGE schule_id schule_id INT DEFAULT NULL');
    }
}
