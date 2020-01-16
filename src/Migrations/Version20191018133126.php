<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191018133126 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE training_camp ADD badge_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE training_camp ADD CONSTRAINT FK_CE0E0573F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id)');
        $this->addSql('CREATE INDEX IDX_CE0E0573F7A2C2FC ON training_camp (badge_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE training_camp DROP FOREIGN KEY FK_CE0E0573F7A2C2FC');
        $this->addSql('DROP INDEX IDX_CE0E0573F7A2C2FC ON training_camp');
        $this->addSql('ALTER TABLE training_camp DROP badge_id');
    }
}
