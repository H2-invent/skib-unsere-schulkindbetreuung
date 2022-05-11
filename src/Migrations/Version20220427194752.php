<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220427194752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dokumete_rechnung (stadt_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_10EDB8A08D9D9BB9 (stadt_id), INDEX IDX_10EDB8A093CB796C (file_id), PRIMARY KEY(stadt_id, file_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dokumete_skib_abmeldung (stadt_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_90C458CC8D9D9BB9 (stadt_id), INDEX IDX_90C458CC93CB796C (file_id), PRIMARY KEY(stadt_id, file_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dokumete_rechnung ADD CONSTRAINT FK_10EDB8A08D9D9BB9 FOREIGN KEY (stadt_id) REFERENCES stadt (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dokumete_rechnung ADD CONSTRAINT FK_10EDB8A093CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dokumete_skib_abmeldung ADD CONSTRAINT FK_90C458CC8D9D9BB9 FOREIGN KEY (stadt_id) REFERENCES stadt (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dokumete_skib_abmeldung ADD CONSTRAINT FK_90C458CC93CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE dokumete_rechnung');
        $this->addSql('DROP TABLE dokumete_skib_abmeldung');
    }
}
