<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190925161147 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player_stats ADD season_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE player_stats ADD CONSTRAINT FK_E8351CEC4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('CREATE INDEX IDX_E8351CEC4EC001D1 ON player_stats (season_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player_stats DROP FOREIGN KEY FK_E8351CEC4EC001D1');
        $this->addSql('DROP INDEX IDX_E8351CEC4EC001D1 ON player_stats');
        $this->addSql('ALTER TABLE player_stats DROP season_id');
    }
}
