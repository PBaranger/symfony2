<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250131174010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire ADD article_id INT NOT NULL');
        $this->addSql('ALTER TABLE commentaire DROP titre');
        $this->addSql('ALTER TABLE commentaire DROP texte');
        $this->addSql('ALTER TABLE commentaire DROP publie');
        $this->addSql('ALTER TABLE commentaire DROP date');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC7294869C FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_67F068BC7294869C ON commentaire (article_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE commentaire DROP CONSTRAINT FK_67F068BC7294869C');
        $this->addSql('DROP INDEX IDX_67F068BC7294869C');
        $this->addSql('ALTER TABLE commentaire ADD titre VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaire ADD texte TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaire ADD publie BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaire ADD date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE commentaire DROP article_id');
        $this->addSql('COMMENT ON COLUMN commentaire.date IS \'(DC2Type:datetime_immutable)\'');
    }
}
