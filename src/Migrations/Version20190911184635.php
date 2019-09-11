<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190911184635 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE abwesend (id INT AUTO_INCREMENT NOT NULL, kind_id INT NOT NULL, zeitblock_id INT DEFAULT NULL, von DATETIME NOT NULL, bis DATETIME NOT NULL, INDEX IDX_A488021530602CA9 (kind_id), INDEX IDX_A488021598597586 (zeitblock_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE active (id INT AUTO_INCREMENT NOT NULL, zeitblock_id INT DEFAULT NULL, von DATETIME NOT NULL, bis DATETIME NOT NULL, UNIQUE INDEX UNIQ_4B1EFC0298597586 (zeitblock_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE anmeldefristen (id INT AUTO_INCREMENT NOT NULL, stadt_id INT NOT NULL, von DATETIME NOT NULL, bis DATETIME NOT NULL, INDEX IDX_5301D0098D9D9BB9 (stadt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kind (id INT AUTO_INCREMENT NOT NULL, eltern_id INT NOT NULL, allergie LONGTEXT DEFAULT NULL, medikamente LONGTEXT DEFAULT NULL, vorname LONGTEXT NOT NULL, nachname LONGTEXT NOT NULL, klasse INT NOT NULL, art INT NOT NULL, geburtstag DATETIME NOT NULL, INDEX IDX_3BC4BCD9F64C20DD (eltern_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE organisation (id INT AUTO_INCREMENT NOT NULL, stadt_id INT DEFAULT NULL, name LONGTEXT NOT NULL, INDEX IDX_E6E132B48D9D9BB9 (stadt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stadt (id INT AUTO_INCREMENT NOT NULL, slug LONGTEXT NOT NULL, name LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE zeitblock (id INT AUTO_INCREMENT NOT NULL, schule_id INT DEFAULT NULL, INDEX IDX_D02BD5E45BCF5349 (schule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE zeitblock_kind (zeitblock_id INT NOT NULL, kind_id INT NOT NULL, INDEX IDX_1F1F886898597586 (zeitblock_id), INDEX IDX_1F1F886830602CA9 (kind_id), PRIMARY KEY(zeitblock_id, kind_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE abwesend ADD CONSTRAINT FK_A488021530602CA9 FOREIGN KEY (kind_id) REFERENCES kind (id)');
        $this->addSql('ALTER TABLE abwesend ADD CONSTRAINT FK_A488021598597586 FOREIGN KEY (zeitblock_id) REFERENCES zeitblock (id)');
        $this->addSql('ALTER TABLE active ADD CONSTRAINT FK_4B1EFC0298597586 FOREIGN KEY (zeitblock_id) REFERENCES zeitblock (id)');
        $this->addSql('ALTER TABLE anmeldefristen ADD CONSTRAINT FK_5301D0098D9D9BB9 FOREIGN KEY (stadt_id) REFERENCES stadt (id)');
        $this->addSql('ALTER TABLE kind ADD CONSTRAINT FK_3BC4BCD9F64C20DD FOREIGN KEY (eltern_id) REFERENCES stammdaten (id)');
        $this->addSql('ALTER TABLE organisation ADD CONSTRAINT FK_E6E132B48D9D9BB9 FOREIGN KEY (stadt_id) REFERENCES stadt (id)');
        $this->addSql('ALTER TABLE zeitblock ADD CONSTRAINT FK_D02BD5E45BCF5349 FOREIGN KEY (schule_id) REFERENCES schule (id)');
        $this->addSql('ALTER TABLE zeitblock_kind ADD CONSTRAINT FK_1F1F886898597586 FOREIGN KEY (zeitblock_id) REFERENCES zeitblock (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE zeitblock_kind ADD CONSTRAINT FK_1F1F886830602CA9 FOREIGN KEY (kind_id) REFERENCES kind (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE schule ADD organisation_id INT NOT NULL, DROP slug');
        $this->addSql('ALTER TABLE schule ADD CONSTRAINT FK_BADE177A9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
        $this->addSql('CREATE INDEX IDX_BADE177A9E6B1585 ON schule (organisation_id)');
        $this->addSql('ALTER TABLE stammdaten ADD kinder_im_kiga TINYINT(1) NOT NULL, ADD uid LONGTEXT NOT NULL, ADD angemeldet TINYINT(1) NOT NULL, ADD buk TINYINT(1) NOT NULL, ADD berufliche_situation LONGTEXT DEFAULT NULL, ADD notfallkontakt LONGTEXT NOT NULL, ADD sepa_info TINYINT(1) NOT NULL, ADD iban LONGTEXT NOT NULL, CHANGE einkommen einkommen INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE abwesend DROP FOREIGN KEY FK_A488021530602CA9');
        $this->addSql('ALTER TABLE zeitblock_kind DROP FOREIGN KEY FK_1F1F886830602CA9');
        $this->addSql('ALTER TABLE schule DROP FOREIGN KEY FK_BADE177A9E6B1585');
        $this->addSql('ALTER TABLE anmeldefristen DROP FOREIGN KEY FK_5301D0098D9D9BB9');
        $this->addSql('ALTER TABLE organisation DROP FOREIGN KEY FK_E6E132B48D9D9BB9');
        $this->addSql('ALTER TABLE abwesend DROP FOREIGN KEY FK_A488021598597586');
        $this->addSql('ALTER TABLE active DROP FOREIGN KEY FK_4B1EFC0298597586');
        $this->addSql('ALTER TABLE zeitblock_kind DROP FOREIGN KEY FK_1F1F886898597586');
        $this->addSql('DROP TABLE abwesend');
        $this->addSql('DROP TABLE active');
        $this->addSql('DROP TABLE anmeldefristen');
        $this->addSql('DROP TABLE kind');
        $this->addSql('DROP TABLE organisation');
        $this->addSql('DROP TABLE stadt');
        $this->addSql('DROP TABLE zeitblock');
        $this->addSql('DROP TABLE zeitblock_kind');
        $this->addSql('DROP INDEX IDX_BADE177A9E6B1585 ON schule');
        $this->addSql('ALTER TABLE schule ADD slug LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, DROP organisation_id');
        $this->addSql('ALTER TABLE stammdaten DROP kinder_im_kiga, DROP uid, DROP angemeldet, DROP buk, DROP berufliche_situation, DROP notfallkontakt, DROP sepa_info, DROP iban, CHANGE einkommen einkommen INT NOT NULL');
    }
}
