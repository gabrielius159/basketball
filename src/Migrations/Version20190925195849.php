<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190925195849 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE coach (id INT AUTO_INCREMENT NOT NULL, team_id INT DEFAULT NULL, first_game_type_id INT NOT NULL, second_game_type_id INT NOT NULL, third_game_type_id INT NOT NULL, firstname VARCHAR(30) NOT NULL, lastname VARCHAR(30) NOT NULL, UNIQUE INDEX UNIQ_3F596DCC296CD8AE (team_id), INDEX IDX_3F596DCC257AA52D (first_game_type_id), INDEX IDX_3F596DCCAAE82B43 (second_game_type_id), INDEX IDX_3F596DCCEA1EAB52 (third_game_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE coach ADD CONSTRAINT FK_3F596DCC296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE coach ADD CONSTRAINT FK_3F596DCC257AA52D FOREIGN KEY (first_game_type_id) REFERENCES game_type (id)');
        $this->addSql('ALTER TABLE coach ADD CONSTRAINT FK_3F596DCCAAE82B43 FOREIGN KEY (second_game_type_id) REFERENCES game_type (id)');
        $this->addSql('ALTER TABLE coach ADD CONSTRAINT FK_3F596DCCEA1EAB52 FOREIGN KEY (third_game_type_id) REFERENCES game_type (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE coach');
    }
}
