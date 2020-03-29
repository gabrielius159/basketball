<?php

namespace App\Repository;

use App\Entity\Season;
use App\Entity\Server;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Server|null find($id, $lockMode = null, $lockVersion = null)
 * @method Server|null findOneBy(array $criteria, array $orderBy = null)
 * @method Server[]    findAll()
 * @method Server[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Server::class);
    }

    /**
     * @return array
     */
    public function findServersWithActiveSeason(): array
    {
        $statuses = [
            Season::STATUS_ACTIVE,
            Season::STATUS_PREPARING
        ];

        return $this->createQueryBuilder('s')
            ->select('s.id, s.name, season.id AS seasonId, season.status AS seasonStatus')
            ->innerJoin('s.seasons', 'season', 'WITH', 'season.server = s.id AND season.status IN (:statuses)')
            ->setParameter('statuses', $statuses)
            ->getQuery()
            ->getResult();
    }
}
