<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190917200414 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, position_id INT NOT NULL, country_id INT NOT NULL, server_id INT DEFAULT NULL, first_type_id INT NOT NULL, second_type_id INT NOT NULL, firstname VARCHAR(40) NOT NULL, lastname VARCHAR(40) NOT NULL, born DATETIME NOT NULL, is_training TINYINT(1) NOT NULL, energy DOUBLE PRECISION NOT NULL, is_real_player TINYINT(1) NOT NULL, INDEX IDX_98197A65DD842E46 (position_id), INDEX IDX_98197A65F92F3E70 (country_id), INDEX IDX_98197A651844E6B7 (server_id), INDEX IDX_98197A65D6C0E06F (first_type_id), INDEX IDX_98197A65D20E0CFE (second_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65DD842E46 FOREIGN KEY (position_id) REFERENCES position (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A651844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65D6C0E06F FOREIGN KEY (first_type_id) REFERENCES game_type (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65D20E0CFE FOREIGN KEY (second_type_id) REFERENCES game_type (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE player');
    }
}
