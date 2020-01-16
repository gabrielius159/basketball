<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Server;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Creates default server
 */
final class Version20190916204306 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return 'Creates server 1';
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $server = new Server();
        $server->setName(Server::SERVER_ONE);

        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $checkIfExistsServerOne = $em->getRepository(Server::class)->findOneBy(['name' => Server::SERVER_ONE]);

        if(!$checkIfExistsServerOne) {
            $em->persist($server);
            $em->flush();
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {

    }
}
