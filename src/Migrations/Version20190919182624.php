<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Season;
use App\Entity\Server;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190919182624 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return 'Create season 1';
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $server = $em->getRepository(Server::class)->findOneBy(['name' => Server::SERVER_ONE]);

        $season = new Season();

        $season->setStatus(Season::STATUS_PREPARING);
        $season->setServer($server);

        $em->persist($season);
        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {

    }
}
