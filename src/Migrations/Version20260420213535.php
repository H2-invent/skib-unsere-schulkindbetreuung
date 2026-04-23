<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260420213535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rechnung_kind_betrag (id INT AUTO_INCREMENT NOT NULL, kind_id INT NOT NULL, rechnung_id INT NOT NULL, betrag DOUBLE PRECISION NOT NULL, INDEX IDX_D23A50AF30602CA9 (kind_id), INDEX IDX_D23A50AF57222FB (rechnung_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rechnung_kind_betrag ADD CONSTRAINT FK_D23A50AF30602CA9 FOREIGN KEY (kind_id) REFERENCES kind (id)');
        $this->addSql('ALTER TABLE rechnung_kind_betrag ADD CONSTRAINT FK_D23A50AF57222FB FOREIGN KEY (rechnung_id) REFERENCES rechnung (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rechnung_kind_betrag DROP FOREIGN KEY FK_D23A50AF30602CA9');
        $this->addSql('ALTER TABLE rechnung_kind_betrag DROP FOREIGN KEY FK_D23A50AF57222FB');
        $this->addSql('DROP TABLE rechnung_kind_betrag');
    }
}
