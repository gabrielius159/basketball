<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\GameType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191018160356 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $oldGameTypes = $em->getRepository(GameType::class)->findAll();

        if($oldGameTypes) {
            foreach($oldGameTypes as $oldGameType) {
                $em->remove($oldGameType);
                $em->flush();
            }
        }

        $gameTypes = [
            GameType::TYPE_SCORING,
            GameType::TYPE_ASSISTING,
            GameType::TYPE_REBOUNDING,
            GameType::TYPE_STEALING,
            GameType::TYPE_BLOCKING
        ];

        $gameTypeNames = [
            'Scoring',
            'Assisting',
            'Rebounding',
            'Stealing',
            'Blocking'
        ];

        foreach($gameTypes as $key => $gameType) {
            $gameTypeCheck = $em->getRepository(GameType::class)->findOneBy(['type' => $gameType]);

            if(!$gameTypeCheck) {
                $newGameType = (new GameType())->setType($gameType)->setName($gameTypeNames[$key]);

                $em->persist($newGameType);
                $em->flush();
            }
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
