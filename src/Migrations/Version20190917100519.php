<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Country;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Intl\Intl;

/**
 * Creates all countries
 */
final class Version20190917100519 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return 'Creates all countries';
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $countries = Intl::getRegionBundle()->getCountryNames();

        foreach($countries as $code => $country) {
            $countryChecker = $em->getRepository(Country::class)->findOneBy(['name' => $country]);

            if(!$countryChecker) {
                $newCountry = (new Country())->setName($country);

                $em->persist($newCountry);
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
