<?php

namespace App\Repository;

use App\Entity\GameDay;
use App\Entity\Season;
use App\Entity\Server;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GameDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method GameDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method GameDay[]    findAll()
 * @method GameDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameDayRepository extends ServiceEntityRepository
{
    /**
     * GameDayRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameDay::class);
    }

    /**
     * @param \Datetime $date
     * @param Season $season
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getByDate(\Datetime $date, Season $season)
    {
        $from = new \DateTime($date->format("Y-m-d")." 00:00:00");
        $to   = new \DateTime($date->format("Y-m-d")." 23:59:59");

        $qb = $this->createQueryBuilder("gd");
        $qb
            ->where('gd.time BETWEEN :from AND :to')
            ->andWhere('gd.season = :season')
            ->andWhere('gd.status != :status')
            ->setParameters([
                'from' => $from,
                'to' => $to,
                'season' => $season,
                'status' => Season::STATUS_FINISHED
            ])
            ->setMaxResults(1)
        ;

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result;
    }

    /**
     * @param Season $season
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getTwoUpcomingGames(Season $season)
    {
        $today = new \DateTime();
        $from = new \DateTime($today->format("Y-m-d")." 00:00:00");
        $to   = new \DateTime($today->format("Y-m-d")." 23:59:59");

        return $this->createQueryBuilder('gd')
            ->where('gd.season = :season')
            ->andWhere('gd.status = :status')
            ->andWhere('gd.time NOT BETWEEN :from AND :to')
            ->orderBy('gd.time', 'ASC')
            ->setParameters([
                'season' => $season,
                'from' => $from,
                'to' => $to,
                'status' => GameDay::STATUS_WAITING
            ])
            ->setMaxResults(2)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param int $seasonId
     *
     * @return mixed
     */
    public function getTwoUpcomingGamesWithSeasonId(int $seasonId)
    {
        return $this->createQueryBuilder('gd')
            ->where('gd.season = :season')
            ->andWhere('gd.status = :status')
            ->orderBy('gd.time', 'ASC')
            ->setParameters([
                'season' => $seasonId,
                'status' => GameDay::STATUS_WAITING
            ])
            ->setMaxResults(2)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param int $seasonId
     *
     * @return GameDay|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUpcomingGameWithSeasonId(int $seasonId): ?GameDay
    {
        return $this->createQueryBuilder('gd')
            ->where('gd.season = :season')
            ->andWhere('gd.status = :status')
            ->orderBy('gd.time', 'ASC')
            ->setParameters([
                'season' => $seasonId,
                'status' => GameDay::STATUS_WAITING
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @param Season $season
     *
     * @return QueryBuilder
     */
    public function getSchedule(Season $season): QueryBuilder
    {
        return $this->createQueryBuilder('gd')
            ->where('gd.season = :season')
            ->setParameters([
                'season' => $season
            ])
        ;
    }

    /**
     * @param Team $team
     *
     * @return mixed
     */
    public function getTeamGameDays(Team $team)
    {
        $gameDays = $this->createQueryBuilder('gd')
            ->where('gd.teamOne = :team')
            ->orWhere('gd.teamTwo = :team')
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult()
        ;

        return $gameDays;
    }

    // /**
    //  * @return GameDay[] Returns an array of GameDay objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GameDay
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
