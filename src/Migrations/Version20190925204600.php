<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190925204600 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE abwesend DROP FOREIGN KEY FK_A488021598597586');
        $this->addSql('ALTER TABLE active DROP FOREIGN KEY FK_4B1EFC0298597586');
        $this->addSql('ALTER TABLE zeitblock_kind DROP FOREIGN KEY FK_1F1F886898597586');
        $this->addSql('DROP TABLE zeitblock');
        $this->addSql('DROP TABLE zeitblock_kind');
        $this->addSql('DROP INDEX IDX_A488021598597586 ON abwesend');
        $this->addSql('ALTER TABLE abwesend DROP zeitblock_id');
        $this->addSql('DROP INDEX UNIQ_4B1EFC0298597586 ON active');
        $this->addSql('ALTER TABLE active DROP zeitblock_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE zeitblock (id INT AUTO_INCREMENT NOT NULL, schule_id INT DEFAULT NULL, von TIME NOT NULL, bis TIME NOT NULL, preis DOUBLE PRECISION NOT NULL, INDEX IDX_D02BD5E45BCF5349 (schule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE zeitblock_kind (zeitblock_id INT NOT NULL, kind_id INT NOT NULL, INDEX IDX_1F1F886830602CA9 (kind_id), INDEX IDX_1F1F886898597586 (zeitblock_id), PRIMARY KEY(zeitblock_id, kind_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE zeitblock ADD CONSTRAINT FK_D02BD5E45BCF5349 FOREIGN KEY (schule_id) REFERENCES schule (id)');
        $this->addSql('ALTER TABLE zeitblock_kind ADD CONSTRAINT FK_1F1F886830602CA9 FOREIGN KEY (kind_id) REFERENCES kind (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE zeitblock_kind ADD CONSTRAINT FK_1F1F886898597586 FOREIGN KEY (zeitblock_id) REFERENCES zeitblock (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE abwesend ADD zeitblock_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE abwesend ADD CONSTRAINT FK_A488021598597586 FOREIGN KEY (zeitblock_id) REFERENCES zeitblock (id)');
        $this->addSql('CREATE INDEX IDX_A488021598597586 ON abwesend (zeitblock_id)');
        $this->addSql('ALTER TABLE active ADD zeitblock_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE active ADD CONSTRAINT FK_4B1EFC0298597586 FOREIGN KEY (zeitblock_id) REFERENCES zeitblock (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4B1EFC0298597586 ON active (zeitblock_id)');
    }
}
