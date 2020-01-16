<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191023152850 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //$this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE coach DROP FOREIGN KEY FK_3F596DCCAAE82B43');
        $this->addSql('ALTER TABLE coach DROP FOREIGN KEY FK_3F596DCCEA1EAB52');
        $this->addSql('DROP INDEX IDX_3F596DCCEA1EAB52 ON coach');
        $this->addSql('DROP INDEX IDX_3F596DCCAAE82B43 ON coach');
        $this->addSql('ALTER TABLE coach DROP second_game_type_id, DROP third_game_type_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //$this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, headers LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, queue_name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE coach ADD second_game_type_id INT NOT NULL, ADD third_game_type_id INT NOT NULL');
        $this->addSql('ALTER TABLE coach ADD CONSTRAINT FK_3F596DCCAAE82B43 FOREIGN KEY (second_game_type_id) REFERENCES game_type (id)');
        $this->addSql('ALTER TABLE coach ADD CONSTRAINT FK_3F596DCCEA1EAB52 FOREIGN KEY (third_game_type_id) REFERENCES game_type (id)');
        $this->addSql('CREATE INDEX IDX_3F596DCCEA1EAB52 ON coach (third_game_type_id)');
        $this->addSql('CREATE INDEX IDX_3F596DCCAAE82B43 ON coach (second_game_type_id)');
    }
}
