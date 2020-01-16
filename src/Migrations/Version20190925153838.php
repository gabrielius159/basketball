<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190925153838 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE attribute ADD game_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE attribute ADD CONSTRAINT FK_FA7AEFFB508EF3BC FOREIGN KEY (game_type_id) REFERENCES game_type (id)');
        $this->addSql('CREATE INDEX IDX_FA7AEFFB508EF3BC ON attribute (game_type_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE attribute DROP FOREIGN KEY FK_FA7AEFFB508EF3BC');
        $this->addSql('DROP INDEX IDX_FA7AEFFB508EF3BC ON attribute');
        $this->addSql('ALTER TABLE attribute DROP game_type_id');
    }
}
