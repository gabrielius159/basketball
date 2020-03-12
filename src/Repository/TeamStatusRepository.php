<?php

namespace App\Repository;

use App\Entity\Season;
use App\Entity\Server;
use App\Entity\TeamStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Common\Collections\Collection;

/**
 * @method TeamStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamStatus[]    findAll()
 * @method TeamStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamStatusRepository extends ServiceEntityRepository
{
    /**
     * TeamStatusRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamStatus::class);
    }

    /**
     * @param Season $season
     *
     * @return mixed
     */
    public function getStandingsList(Season $season)
    {
        //return $this->getStandingsListTest($season);
        return $this->createQueryBuilder('ts')
            ->where('ts.season = :season')
            ->orderBy('ts.win', 'DESC')
            ->addOrderBy('ts.lose', 'ASC')
            ->setParameter('season', $season)
            ->getQuery()
            ->getResult()
            ;
    }

    public function getStandingsListTest(Season $season)
    {
        return $this->createQueryBuilder('ts')
            ->where('ts.season = :season')
            ->orderBy('ts.win', 'DESC')
            ->addOrderBy('ts.lose', 'ASC')
            ->setParameter('season', $season)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param Season $season
     *
     * @return null|TeamStatus
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getChampions(Season $season)
    {
        return $this->createQueryBuilder('ts')
            ->where('ts.season = :season')
            ->orderBy('ts.win', 'DESC')
            ->orderBy('ts.lose', 'ASC')
            ->setParameter('season', $season)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param Season $season
     * @param TeamStatus $teamStatus
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function championsCheck(Season $season, TeamStatus $teamStatus)
    {
        return $this->createQueryBuilder('ts')
            ->select('count(ts.id)')
            ->where('ts.season = :season')
            ->andWhere('ts.win = :win')
            ->andWhere('ts.lose = :lose')
            ->andWhere('ts.id != :teamStatus')
            ->setParameters([
                'season' => $season,
                'win' => $teamStatus->getWin(),
                'lose' => $teamStatus->getLose(),
                'teamStatus' => $teamStatus->getId()
            ])
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    /**
     * @param Season $season
     * @param TeamStatus $teamStatus
     *
     * @return null|TeamStatus
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getChampionsByPoints(Season $season, TeamStatus $teamStatus)
    {
        return $this->createQueryBuilder('ts')
            ->where('ts.season = :season')
            ->andWhere('ts.win = :win')
            ->andWhere('ts.lose = :lose')
            ->orderBy('ts.points', 'DESC')
            ->setParameters([
                'season' => $season,
                'win' => $teamStatus->getWin(),
                'lose' => $teamStatus->getLose()
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return TeamStatus[] Returns an array of TeamStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TeamStatus
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
