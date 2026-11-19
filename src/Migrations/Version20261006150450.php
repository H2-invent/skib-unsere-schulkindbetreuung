<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261006150450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_kinder_rechnung_kind ON kinder_rechnung');
        $this->addSql('ALTER TABLE kinder_rechnung DROP FOREIGN KEY FK_97A93CF86E062231');
        $this->addSql('ALTER TABLE kinder_rechnung DROP FOREIGN KEY FK_97A93CF8F7E8C5FC');
        $this->addSql('ALTER TABLE kinder_rechnung DROP brutto_summe, DROP rabatt');
        $this->addSql('DROP INDEX idx_97a93cf8f7e8c5fc ON kinder_rechnung');
        $this->addSql('CREATE INDEX IDX_79B5DCFC57222FB ON kinder_rechnung (rechnung_id)');
        $this->addSql('DROP INDEX idx_97a93cf86e062231 ON kinder_rechnung');
        $this->addSql('CREATE INDEX IDX_79B5DCFC30602CA9 ON kinder_rechnung (kind_id)');
        $this->addSql('ALTER TABLE kinder_rechnung ADD CONSTRAINT FK_97A93CF86E062231 FOREIGN KEY (kind_id) REFERENCES kind (id)');
        $this->addSql('ALTER TABLE kinder_rechnung ADD CONSTRAINT FK_97A93CF8F7E8C5FC FOREIGN KEY (rechnung_id) REFERENCES rechnung (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kinder_rechnung DROP FOREIGN KEY FK_79B5DCFC57222FB');
        $this->addSql('ALTER TABLE kinder_rechnung DROP FOREIGN KEY FK_79B5DCFC30602CA9');
        $this->addSql('ALTER TABLE kinder_rechnung ADD brutto_summe NUMERIC(10, 2) NOT NULL, ADD rabatt NUMERIC(10, 2) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_kinder_rechnung_kind ON kinder_rechnung (rechnung_id, kind_id)');
        $this->addSql('DROP INDEX idx_79b5dcfc30602ca9 ON kinder_rechnung');
        $this->addSql('CREATE INDEX IDX_97A93CF86E062231 ON kinder_rechnung (kind_id)');
        $this->addSql('DROP INDEX idx_79b5dcfc57222fb ON kinder_rechnung');
        $this->addSql('CREATE INDEX IDX_97A93CF8F7E8C5FC ON kinder_rechnung (rechnung_id)');
        $this->addSql('ALTER TABLE kinder_rechnung ADD CONSTRAINT FK_79B5DCFC57222FB FOREIGN KEY (rechnung_id) REFERENCES rechnung (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kinder_rechnung ADD CONSTRAINT FK_79B5DCFC30602CA9 FOREIGN KEY (kind_id) REFERENCES kind (id)');
    }
}
