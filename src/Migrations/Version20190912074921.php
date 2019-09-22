<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190912074921 extends AbstractMigration
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
        $this->addSql('CREATE TABLE organisation (id INT AUTO_INCREMENT NOT NULL, stadt_id INT DEFAULT NULL, name LONGTEXT NOT NULL, INDEX IDX_E6E132B48D9D9BB9 (stadt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stadt (id INT AUTO_INCREMENT NOT NULL, slug LONGTEXT NOT NULL, name LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_957A647992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_957A6479A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_957A6479C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE zeitblock_kind (zeitblock_id INT NOT NULL, kind_id INT NOT NULL, INDEX IDX_1F1F886898597586 (zeitblock_id), INDEX IDX_1F1F886830602CA9 (kind_id), PRIMARY KEY(zeitblock_id, kind_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE abwesend ADD CONSTRAINT FK_A488021530602CA9 FOREIGN KEY (kind_id) REFERENCES kind (id)');
        $this->addSql('ALTER TABLE abwesend ADD CONSTRAINT FK_A488021598597586 FOREIGN KEY (zeitblock_id) REFERENCES zeitblock (id)');
        $this->addSql('ALTER TABLE active ADD CONSTRAINT FK_4B1EFC0298597586 FOREIGN KEY (zeitblock_id) REFERENCES zeitblock (id)');
        $this->addSql('ALTER TABLE anmeldefristen ADD CONSTRAINT FK_5301D0098D9D9BB9 FOREIGN KEY (stadt_id) REFERENCES stadt (id)');
        $this->addSql('ALTER TABLE organisation ADD CONSTRAINT FK_E6E132B48D9D9BB9 FOREIGN KEY (stadt_id) REFERENCES stadt (id)');
        $this->addSql('ALTER TABLE zeitblock_kind ADD CONSTRAINT FK_1F1F886898597586 FOREIGN KEY (zeitblock_id) REFERENCES zeitblock (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE zeitblock_kind ADD CONSTRAINT FK_1F1F886830602CA9 FOREIGN KEY (kind_id) REFERENCES kind (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE schule CHANGE stadt_id stadt_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE schule ADD CONSTRAINT FK_BADE177A9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
        $this->addSql('ALTER TABLE schule ADD CONSTRAINT FK_BADE177A8D9D9BB9 FOREIGN KEY (stadt_id) REFERENCES stadt (id)');
        $this->addSql('CREATE INDEX IDX_BADE177A9E6B1585 ON schule (organisation_id)');
        $this->addSql('CREATE INDEX IDX_BADE177A8D9D9BB9 ON schule (stadt_id)');
        $this->addSql('ALTER TABLE stammdaten ADD kinder_im_kiga TINYINT(1) NOT NULL, ADD uid LONGTEXT NOT NULL, ADD angemeldet TINYINT(1) NOT NULL, ADD buk TINYINT(1) NOT NULL, ADD berufliche_situation LONGTEXT DEFAULT NULL, ADD notfallkontakt LONGTEXT NOT NULL, ADD sepa_info TINYINT(1) NOT NULL, ADD iban LONGTEXT NOT NULL, CHANGE einkommen einkommen INT DEFAULT NULL');
        $this->addSql('ALTER TABLE zeitblock CHANGE schule_id schule_id INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE schule DROP FOREIGN KEY FK_BADE177A9E6B1585');
        $this->addSql('ALTER TABLE anmeldefristen DROP FOREIGN KEY FK_5301D0098D9D9BB9');
        $this->addSql('ALTER TABLE organisation DROP FOREIGN KEY FK_E6E132B48D9D9BB9');
        $this->addSql('ALTER TABLE schule DROP FOREIGN KEY FK_BADE177A8D9D9BB9');
        $this->addSql('DROP TABLE abwesend');
        $this->addSql('DROP TABLE active');
        $this->addSql('DROP TABLE anmeldefristen');
        $this->addSql('DROP TABLE organisation');
        $this->addSql('DROP TABLE stadt');
        $this->addSql('DROP TABLE fos_user');
        $this->addSql('DROP TABLE zeitblock_kind');
        $this->addSql('DROP INDEX IDX_BADE177A9E6B1585 ON schule');
        $this->addSql('DROP INDEX IDX_BADE177A8D9D9BB9 ON schule');
        $this->addSql('ALTER TABLE schule CHANGE stadt_id stadt_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stammdaten DROP kinder_im_kiga, DROP uid, DROP angemeldet, DROP buk, DROP berufliche_situation, DROP notfallkontakt, DROP sepa_info, DROP iban, CHANGE einkommen einkommen INT NOT NULL');
        $this->addSql('ALTER TABLE zeitblock CHANGE schule_id schule_id INT DEFAULT NULL');
    }
}
