<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190925205511 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE zeitblock (id INT AUTO_INCREMENT NOT NULL, schule_id INT NOT NULL, active_id INT DEFAULT NULL, von TIME NOT NULL, bis TIME NOT NULL, INDEX IDX_D02BD5E45BCF5349 (schule_id), UNIQUE INDEX UNIQ_D02BD5E427C382C7 (active_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE zeitblock ADD CONSTRAINT FK_D02BD5E45BCF5349 FOREIGN KEY (schule_id) REFERENCES schule (id)');
        $this->addSql('ALTER TABLE zeitblock ADD CONSTRAINT FK_D02BD5E427C382C7 FOREIGN KEY (active_id) REFERENCES active (id)');
        $this->addSql('ALTER TABLE abwesend CHANGE zeitblock_id zeitblock_id INT NOT NULL');
        $this->addSql('ALTER TABLE active DROP FOREIGN KEY FK_4B1EFC0298597586');
        $this->addSql('DROP INDEX UNIQ_4B1EFC0298597586 ON active');
        $this->addSql('ALTER TABLE active DROP zeitblock_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE abwesend DROP FOREIGN KEY FK_A488021598597586');
        $this->addSql('ALTER TABLE zeitblock_kind DROP FOREIGN KEY FK_1F1F886898597586');
        $this->addSql('DROP TABLE zeitblock');
        $this->addSql('ALTER TABLE abwesend CHANGE zeitblock_id zeitblock_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE active ADD zeitblock_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE active ADD CONSTRAINT FK_4B1EFC0298597586 FOREIGN KEY (zeitblock_id) REFERENCES zeitblock (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4B1EFC0298597586 ON active (zeitblock_id)');
    }
}
