<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260410131323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE child_sick_report DROP FOREIGN KEY FK_B85D0D4E12ADB9D2');
        $this->addSql('ALTER TABLE child_sick_report DROP FOREIGN KEY FK_B85D0D4E36AADE0');
        $this->addSql('DROP INDEX idx_b85d0d4e12adb9d2 ON child_sick_report');
        $this->addSql('CREATE INDEX IDX_B32E837F30602CA9 ON child_sick_report (kind_id)');
        $this->addSql('DROP INDEX idx_b85d0d4e36aade0 ON child_sick_report');
        $this->addSql('CREATE INDEX IDX_B32E837F4FEA67CF ON child_sick_report (access_id)');
        $this->addSql('ALTER TABLE child_sick_report ADD CONSTRAINT FK_B85D0D4E12ADB9D2 FOREIGN KEY (kind_id) REFERENCES kind (id)');
        $this->addSql('ALTER TABLE child_sick_report ADD CONSTRAINT FK_B85D0D4E36AADE0 FOREIGN KEY (access_id) REFERENCES parent_sick_portal_access (id)');
        $this->addSql('ALTER TABLE parent_sick_portal_access DROP FOREIGN KEY FK_2F4D3B36D9555');
        $this->addSql('ALTER TABLE parent_sick_portal_access DROP FOREIGN KEY FK_2F4D3B9D9D9BB9');
        $this->addSql('DROP INDEX idx_2f4d3b9d9d9bb9 ON parent_sick_portal_access');
        $this->addSql('CREATE INDEX IDX_F6AE439C8D9D9BB9 ON parent_sick_portal_access (stadt_id)');
        $this->addSql('DROP INDEX idx_2f4d3b36d9555 ON parent_sick_portal_access');
        $this->addSql('CREATE INDEX IDX_F6AE439C36D9555 ON parent_sick_portal_access (schuljahr_id)');
        $this->addSql('DROP INDEX idx_2f4d3be7927c74 ON parent_sick_portal_access');
        $this->addSql('CREATE INDEX IDX_F6AE439CE7927C74 ON parent_sick_portal_access (email)');
        $this->addSql('ALTER TABLE parent_sick_portal_access ADD CONSTRAINT FK_2F4D3B36D9555 FOREIGN KEY (schuljahr_id) REFERENCES active (id)');
        $this->addSql('ALTER TABLE parent_sick_portal_access ADD CONSTRAINT FK_2F4D3B9D9D9BB9 FOREIGN KEY (stadt_id) REFERENCES stadt (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE child_sick_report DROP FOREIGN KEY FK_B32E837F30602CA9');
        $this->addSql('ALTER TABLE child_sick_report DROP FOREIGN KEY FK_B32E837F4FEA67CF');
        $this->addSql('DROP INDEX idx_b32e837f30602ca9 ON child_sick_report');
        $this->addSql('CREATE INDEX IDX_B85D0D4E12ADB9D2 ON child_sick_report (kind_id)');
        $this->addSql('DROP INDEX idx_b32e837f4fea67cf ON child_sick_report');
        $this->addSql('CREATE INDEX IDX_B85D0D4E36AADE0 ON child_sick_report (access_id)');
        $this->addSql('ALTER TABLE child_sick_report ADD CONSTRAINT FK_B32E837F30602CA9 FOREIGN KEY (kind_id) REFERENCES kind (id)');
        $this->addSql('ALTER TABLE child_sick_report ADD CONSTRAINT FK_B32E837F4FEA67CF FOREIGN KEY (access_id) REFERENCES parent_sick_portal_access (id)');
        $this->addSql('ALTER TABLE parent_sick_portal_access DROP FOREIGN KEY FK_F6AE439C8D9D9BB9');
        $this->addSql('ALTER TABLE parent_sick_portal_access DROP FOREIGN KEY FK_F6AE439C36D9555');
        $this->addSql('DROP INDEX idx_f6ae439ce7927c74 ON parent_sick_portal_access');
        $this->addSql('CREATE INDEX IDX_2F4D3BE7927C74 ON parent_sick_portal_access (email)');
        $this->addSql('DROP INDEX idx_f6ae439c8d9d9bb9 ON parent_sick_portal_access');
        $this->addSql('CREATE INDEX IDX_2F4D3B9D9D9BB9 ON parent_sick_portal_access (stadt_id)');
        $this->addSql('DROP INDEX idx_f6ae439c36d9555 ON parent_sick_portal_access');
        $this->addSql('CREATE INDEX IDX_2F4D3B36D9555 ON parent_sick_portal_access (schuljahr_id)');
        $this->addSql('ALTER TABLE parent_sick_portal_access ADD CONSTRAINT FK_F6AE439C8D9D9BB9 FOREIGN KEY (stadt_id) REFERENCES stadt (id)');
        $this->addSql('ALTER TABLE parent_sick_portal_access ADD CONSTRAINT FK_F6AE439C36D9555 FOREIGN KEY (schuljahr_id) REFERENCES active (id)');
    }
}
