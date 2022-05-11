<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220427185947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dokumente_confirm (stadt_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_CC35AB8D9D9BB9 (stadt_id), INDEX IDX_CC35AB93CB796C (file_id), PRIMARY KEY(stadt_id, file_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dokumete_skib_anmeldung (stadt_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_C76B4D838D9D9BB9 (stadt_id), INDEX IDX_C76B4D8393CB796C (file_id), PRIMARY KEY(stadt_id, file_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dokumete_skib_buchung (stadt_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_E955EBF48D9D9BB9 (stadt_id), INDEX IDX_E955EBF493CB796C (file_id), PRIMARY KEY(stadt_id, file_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dokumete_skib_anderung (stadt_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_E9488C278D9D9BB9 (stadt_id), INDEX IDX_E9488C2793CB796C (file_id), PRIMARY KEY(stadt_id, file_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dokumente_confirm ADD CONSTRAINT FK_CC35AB8D9D9BB9 FOREIGN KEY (stadt_id) REFERENCES stadt (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dokumente_confirm ADD CONSTRAINT FK_CC35AB93CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dokumete_skib_anmeldung ADD CONSTRAINT FK_C76B4D838D9D9BB9 FOREIGN KEY (stadt_id) REFERENCES stadt (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dokumete_skib_anmeldung ADD CONSTRAINT FK_C76B4D8393CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dokumete_skib_buchung ADD CONSTRAINT FK_E955EBF48D9D9BB9 FOREIGN KEY (stadt_id) REFERENCES stadt (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dokumete_skib_buchung ADD CONSTRAINT FK_E955EBF493CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dokumete_skib_anderung ADD CONSTRAINT FK_E9488C278D9D9BB9 FOREIGN KEY (stadt_id) REFERENCES stadt (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dokumete_skib_anderung ADD CONSTRAINT FK_E9488C2793CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE dokumente_confirm');
        $this->addSql('DROP TABLE dokumete_skib_anmeldung');
        $this->addSql('DROP TABLE dokumete_skib_buchung');
        $this->addSql('DROP TABLE dokumete_skib_anderung');
    }
}
