<?php

namespace App\Repository;

use App\Entity\Season;
use App\Entity\Server;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Season|null find($id, $lockMode = null, $lockVersion = null)
 * @method Season|null findOneBy(array $criteria, array $orderBy = null)
 * @method Season[]    findAll()
 * @method Season[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeasonRepository extends ServiceEntityRepository
{
    /**
     * SeasonRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Season::class);
    }

    /**
     * @param Server $server
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getActiveSeason(Server $server)
    {
        return $this->createQueryBuilder('s')
            ->where('s.status = :active')
            ->orWhere('s.status = :preparing')
            ->andWhere('s.server = :server')
            ->setParameters([
                'active' => Season::STATUS_ACTIVE,
                'preparing' => Season::STATUS_PREPARING,
                'server' => $server
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Server $server
     *
     * @return mixed
     */
    public function findAllSeasonIds(Server $server)
    {
        return $this->createQueryBuilder('s')
            ->select('s.id')
            ->where('s.server = :server')
            ->setParameter('server', $server)
            ->getQuery()
            ->getResult();
    }
}
