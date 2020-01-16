<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Position;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190916214802 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return 'Create 5 default positions';
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $positions = [
            Position::POINT_GUARD,
            Position::SHOOTING_GUARD,
            Position::SMALL_FORWARD,
            Position::POWER_FORWARD,
            Position::CENTER
        ];

        $em = $this->container->get('doctrine.orm.default_entity_manager');

        foreach($positions as $position) {
            $positionCheck = $em->getRepository(Position::class)->findOneBy(['name' => $position]);

            if(!$positionCheck) {
                $pos = new Position();
                $pos->setName($position);

                $em->persist($pos);
                $em->flush();
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
