<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260826123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Speichert den Rechnungsbetrag und Rabatt nachvollziehbar je Kind';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE kinder_rechnung (id INT AUTO_INCREMENT NOT NULL, rechnung_id INT NOT NULL, kind_id INT NOT NULL, summe NUMERIC(10, 2) NOT NULL, brutto_summe NUMERIC(10, 2) NOT NULL, rabatt NUMERIC(10, 2) NOT NULL, INDEX IDX_97A93CF8F7E8C5FC (rechnung_id), INDEX IDX_97A93CF86E062231 (kind_id), UNIQUE INDEX uniq_kinder_rechnung_kind (rechnung_id, kind_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kinder_rechnung ADD CONSTRAINT FK_97A93CF8F7E8C5FC FOREIGN KEY (rechnung_id) REFERENCES rechnung (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kinder_rechnung ADD CONSTRAINT FK_97A93CF86E062231 FOREIGN KEY (kind_id) REFERENCES kind (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE kinder_rechnung');
    }
}
