<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191008154917 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE game_day (id INT AUTO_INCREMENT NOT NULL, season_id INT NOT NULL, team_one_id INT NOT NULL, team_two_id INT NOT NULL, time DATETIME NOT NULL, status VARCHAR(40) NOT NULL, INDEX IDX_FEFA3A554EC001D1 (season_id), INDEX IDX_FEFA3A558D8189CA (team_one_id), INDEX IDX_FEFA3A55E6DD6E05 (team_two_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE game_day ADD CONSTRAINT FK_FEFA3A554EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE game_day ADD CONSTRAINT FK_FEFA3A558D8189CA FOREIGN KEY (team_one_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE game_day ADD CONSTRAINT FK_FEFA3A55E6DD6E05 FOREIGN KEY (team_two_id) REFERENCES team (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE game_day');
    }
}
