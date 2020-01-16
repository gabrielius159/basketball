<?php

namespace App\Repository;

use App\Entity\Player;
use App\Entity\PlayerStats;
use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PlayerStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerStats[]    findAll()
 * @method PlayerStats[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerStatsRepository extends ServiceEntityRepository
{
    /**
     * PlayerStatsRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PlayerStats::class);
    }

    /**
     * @param Season $season
     *
     * @return PlayerStats
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getSeasonMVP(Season $season): PlayerStats
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.season = :season')
            ->orderBy('ps.gamesPlayed', 'DESC')
            ->orderBy('((ps.points*3)+(ps.rebounds*6)+(ps.assists*6))', 'DESC')
            ->setParameter('season', $season)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Season $season
     *
     * @return PlayerStats
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getSeasonDPOY(Season $season): PlayerStats
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.season = :season')
            ->orderBy('ps.gamesPlayed', 'DESC')
            ->orderBy('((ps.rebounds*2)+(ps.blocks*20)+(ps.steals*20))', 'DESC')
            ->setParameter('season', $season)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param Season $season
     * @param string $category
     *
     * @return PlayerStats|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLeadersByCategory(Season $season, string $category)
    {
        $qb = $this->createQueryBuilder('ps')
            ->where('ps.season = :season');

        switch($category) {
            case $category == PlayerStats::CATEGORY_POINTS: {
                $qb->orderBy('ps.points', 'DESC');

                break;
            }
            case $category == PlayerStats::CATEGORY_REBOUNDS: {
                $qb->orderBy('ps.rebounds', 'DESC');

                break;
            }
            case $category == PlayerStats::CATEGORY_ASSISTS: {
                $qb->orderBy('ps.assists', 'DESC');

                break;
            }
            case $category == PlayerStats::CATEGORY_STEALS: {
                $qb->orderBy('ps.steals', 'DESC');

                break;
            }
            case $category == PlayerStats::CATEGORY_BLOCKS: {
                $qb->orderBy('ps.blocks', 'DESC');

                break;
            }
        }

        $leaders = $qb
            ->addOrderBy('ps.gamesPlayed', 'DESC')
            ->setParameter('season', $season)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $leaders;
    }

    /**
     * @param Player $player
     *
     * @return array|null
     */
    public function getCareerPlayerStats(Player $player): ?array
    {
        return $this->createQueryBuilder('ps')
            ->select('ps.gamesPlayed AS gamesPlayed, (ps.points / ps.gamesPlayed) AS pointsAvg, (ps.rebounds / ps.gamesPlayed) AS reboundsAvg, (ps.assists / ps.gamesPlayed) AS assistsAvg, 
            (ps.steals / ps.gamesPlayed) AS stealsAvg, (ps.blocks / ps.gamesPlayed) AS blocksAvg, season.id AS seasonId')
            ->leftJoin('ps.season', 'season')
            ->where('ps.player = :player')
            ->setParameter('player', $player)
            ->orderBy('seasonId', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }
}
