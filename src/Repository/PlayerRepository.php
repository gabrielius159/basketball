<?php

namespace App\Repository;

use App\Entity\Player;
use App\Entity\Season;
use App\Entity\Server;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Player|null find($id, $lockMode = null, $lockVersion = null)
 * @method Player|null findOneBy(array $criteria, array $orderBy = null)
 * @method Player[]    findAll()
 * @method Player[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerRepository extends ServiceEntityRepository
{
    /**
     * PlayerRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Player::class);
    }

    /**
     * @param string|null $keyword
     *
     * @return QueryBuilder
     */
    public function findAllWithQueryBuilder(?string $keyword): QueryBuilder
    {
        if($keyword) {
            return $this->createQueryBuilder('p')
                ->where('p.firstname LIKE :keyword')
                ->orWhere('p.lastname LIKE :keyword')
                ->orderBy('p.id')
                ->setParameter('keyword', '%' . $keyword . '%')
                ;
        }

        return $this->createQueryBuilder('p')
            ->orderBy('p.id')
            ;
    }

    /**
     * @param Team $team
     *
     * @return array
     */
    public function getTakenJerseyNumbers(Team $team): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.jerseyNumber')
            ->where('p.team = :team')
            ->setParameter('team', $team)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * @return QueryBuilder
     */
    public function getFreeAgents(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->where('p.team IS NULL')
            ->andWhere('p.isRealPlayer = :realPlayer')
            ->setParameter('realPlayer', true)
            ;
    }

    /**
     * @param Server $server
     * @param int $season
     *
     * @return mixed
     */
    public function getRealPlayersWithExpiringContract(Server $server, int $season)
    {
        return $this->createQueryBuilder('p')
            ->where('p.isRealPlayer = :realPlayer')
            ->andWhere('p.server = :server')
            ->andWhere('p.seasonEndsContract <= :season')
            ->setParameters([
                'realPlayer' => true,
                'server' => $server,
                'season' => $season
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Season $season
     *
     * @return Player|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getSeasonROTY(Season $season)
    {
        $rookies = $this->getRookiesIds();

        return $this->createQueryBuilder('p')
            ->where('p.id IN (:rookies)')
            ->leftJoin('p.playerStats', 'ps', 'WITH', 'ps.season = :season')
            ->orderBy('ps.gamesPlayed', 'DESC')
            ->orderBy('((ps.points*3)+(ps.rebounds*6)+(ps.assists*6)+(ps.steals*20)+(ps.blocks*20))', 'DESC')
            ->setParameters([
                'season' => $season,
                'rookies' => $rookies
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return array
     */
    public function getRookiesIds(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.id')
            ->innerJoin('p.playerStats', 'ps')
            ->having('COUNT(ps.id) < 2')
            ->groupBy('p.id')
            ->getQuery()
            ->getArrayResult()
            ;
    }

    /**
     * @param bool $real
     *
     * @return Player
     */
    public function findAllForDelete(bool $real = true)
    {
        return $this->createQueryBuilder('p')
            ->where('p.isRealPlayer = :bool')
            ->setParameter('bool', $real)
            ->getQuery()
            ->getResult()
        ;
    }
}
