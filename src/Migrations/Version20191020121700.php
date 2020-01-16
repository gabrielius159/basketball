<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Attribute;
use App\Entity\GameType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191020121700 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return '';
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $scoringGameType = $em->getRepository(GameType::class)->findOneBy([
            'type' => GameType::TYPE_SCORING
        ]);

        $assistingType = $em->getRepository(GameType::class)->findOneBy([
            'type' => GameType::TYPE_ASSISTING
        ]);

        $reboundingType = $em->getRepository(GameType::class)->findOneBy([
            'type' => GameType::TYPE_REBOUNDING
        ]);

        $stealingType = $em->getRepository(GameType::class)->findOneBy([
            'type' => GameType::TYPE_STEALING
        ]);

        $blockingType = $em->getRepository(GameType::class)->findOneBy([
            'type' => GameType::TYPE_BLOCKING
        ]);

        $attributes = [
            '2pt shooting' => $scoringGameType,
            '3pt shooting' => $scoringGameType,
            'Passing' => $assistingType,
            'Floor vision' => $assistingType,
            'Rebounding' => $reboundingType,
            'Box out' => $reboundingType,
            'Steal' => $stealingType,
            'Perimeter steal' => $stealingType,
            'Block' => $blockingType,
            'Perimeter block' => $blockingType
        ];

        foreach($attributes as $key => $value) {
            $newAttribute = (new Attribute())
                ->setName($key)
                ->setGameType($value)
                ->setDefaultValue(25)
            ;

            $em->persist($newAttribute);
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
