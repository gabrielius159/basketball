<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191024115729 extends AbstractMigration
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
        $this->addSql('ALTER TABLE user_reward ADD updated_mvp_at DATETIME DEFAULT NULL, ADD image_mvp_size INT NOT NULL, ADD updated_dpoy_at DATETIME DEFAULT NULL, ADD image_dpoy_size INT NOT NULL, DROP updated_mvpat, DROP image_mvpsize, DROP updated_dpoyat, DROP image_dpoysize');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        //$this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, headers LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, queue_name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_reward ADD updated_mvpat DATETIME DEFAULT NULL, ADD image_mvpsize INT NOT NULL, ADD updated_dpoyat DATETIME DEFAULT NULL, ADD image_dpoysize INT NOT NULL, DROP updated_mvp_at, DROP image_mvp_size, DROP updated_dpoy_at, DROP image_dpoy_size');
    }
}
