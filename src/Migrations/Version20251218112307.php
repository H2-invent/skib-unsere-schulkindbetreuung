<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251218112307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE auto_block_assignment (id INT AUTO_INCREMENT NOT NULL, organisation_id INT NOT NULL, UNIQUE INDEX UNIQ_E4ABAE2F9E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE auto_block_assignment_child (id INT AUTO_INCREMENT NOT NULL, auto_block_assignment_id INT NOT NULL, kind_id INT NOT NULL, weight DOUBLE PRECISION NOT NULL, INDEX IDX_A5E7C8D924D02F0E (auto_block_assignment_id), UNIQUE INDEX UNIQ_A5E7C8D930602CA9 (kind_id), INDEX IDX_A5E7C8D97CD5541 (weight), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE auto_block_assignment_child_zeitblock (id INT AUTO_INCREMENT NOT NULL, child_id INT NOT NULL, zeitblock_id INT NOT NULL, accepted TINYINT(1) DEFAULT 0 NOT NULL, warteschlange TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_A12AA19BDD62C21B (child_id), INDEX IDX_A12AA19B98597586 (zeitblock_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE auto_block_assignment ADD CONSTRAINT FK_E4ABAE2F9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
        $this->addSql('ALTER TABLE auto_block_assignment_child ADD CONSTRAINT FK_A5E7C8D924D02F0E FOREIGN KEY (auto_block_assignment_id) REFERENCES auto_block_assignment (id)');
        $this->addSql('ALTER TABLE auto_block_assignment_child ADD CONSTRAINT FK_A5E7C8D930602CA9 FOREIGN KEY (kind_id) REFERENCES kind (id)');
        $this->addSql('ALTER TABLE auto_block_assignment_child_zeitblock ADD CONSTRAINT FK_A12AA19BDD62C21B FOREIGN KEY (child_id) REFERENCES auto_block_assignment_child (id)');
        $this->addSql('ALTER TABLE auto_block_assignment_child_zeitblock ADD CONSTRAINT FK_A12AA19B98597586 FOREIGN KEY (zeitblock_id) REFERENCES zeitblock (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auto_block_assignment DROP FOREIGN KEY FK_E4ABAE2F9E6B1585');
        $this->addSql('ALTER TABLE auto_block_assignment_child DROP FOREIGN KEY FK_A5E7C8D924D02F0E');
        $this->addSql('ALTER TABLE auto_block_assignment_child DROP FOREIGN KEY FK_A5E7C8D930602CA9');
        $this->addSql('ALTER TABLE auto_block_assignment_child_zeitblock DROP FOREIGN KEY FK_A12AA19BDD62C21B');
        $this->addSql('ALTER TABLE auto_block_assignment_child_zeitblock DROP FOREIGN KEY FK_A12AA19B98597586');
        $this->addSql('DROP TABLE auto_block_assignment');
        $this->addSql('DROP TABLE auto_block_assignment_child');
        $this->addSql('DROP TABLE auto_block_assignment_child_zeitblock');
    }
}
