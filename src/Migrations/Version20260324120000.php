<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds parent sick portal access and child sick reports';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE parent_sick_portal_access (id INT AUTO_INCREMENT NOT NULL, stadt_id INT NOT NULL, schuljahr_id INT NOT NULL, email VARCHAR(255) NOT NULL, uri VARCHAR(512) NOT NULL, token BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', last_used_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_2F4D3B9D9D9BB9 (stadt_id), INDEX IDX_2F4D3B36D9555 (schuljahr_id), INDEX IDX_2F4D3BE7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE child_sick_report (id INT AUTO_INCREMENT NOT NULL, kind_id INT NOT NULL, access_id INT NOT NULL, von DATE NOT NULL COMMENT '(DC2Type:date)', bis DATE NOT NULL COMMENT '(DC2Type:date)', bemerkung LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_B85D0D4E12ADB9D2 (kind_id), INDEX IDX_B85D0D4E36AADE0 (access_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE parent_sick_portal_access ADD CONSTRAINT FK_2F4D3B9D9D9BB9 FOREIGN KEY (stadt_id) REFERENCES stadt (id)');
        $this->addSql('ALTER TABLE parent_sick_portal_access ADD CONSTRAINT FK_2F4D3B36D9555 FOREIGN KEY (schuljahr_id) REFERENCES active (id)');
        $this->addSql('ALTER TABLE child_sick_report ADD CONSTRAINT FK_B85D0D4E12ADB9D2 FOREIGN KEY (kind_id) REFERENCES kind (id)');
        $this->addSql('ALTER TABLE child_sick_report ADD CONSTRAINT FK_B85D0D4E36AADE0 FOREIGN KEY (access_id) REFERENCES parent_sick_portal_access (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE child_sick_report DROP FOREIGN KEY FK_B85D0D4E12ADB9D2');
        $this->addSql('ALTER TABLE child_sick_report DROP FOREIGN KEY FK_B85D0D4E36AADE0');
        $this->addSql('ALTER TABLE parent_sick_portal_access DROP FOREIGN KEY FK_2F4D3B9D9D9BB9');
        $this->addSql('ALTER TABLE parent_sick_portal_access DROP FOREIGN KEY FK_2F4D3B36D9555');
        $this->addSql('DROP TABLE child_sick_report');
        $this->addSql('DROP TABLE parent_sick_portal_access');
    }
}
