<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Player;
use App\Entity\Server;
use App\Message\CheckContracts;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class CheckContractsHandler
 *
 * @package App\MessageHandler
 */
class CheckContractsHandler implements MessageHandlerInterface
{
    const BATCH_SIZE = 50;

    private $entityManager;
    private $playerRepository;

    /**
     * CheckContractsHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param PlayerRepository       $playerRepository
     */
    public function __construct(EntityManagerInterface $entityManager, PlayerRepository $playerRepository)
    {
        $this->entityManager = $entityManager;
        $this->playerRepository = $playerRepository;
    }

    /**
     * @param CheckContracts $message
     */
    public function __invoke(CheckContracts $message)
    {
        $i = 1;

        $server = $this->entityManager->getRepository(Server::class)->find($message->getServerId());
        $playersWithExpiringContractQuery = $this->playerRepository->getRealPlayersWithExpiringContract(
            $server,
            $message->getSeasonId(),
            true
        );

        $iterableResult = $playersWithExpiringContractQuery->iterate();

        foreach ($iterableResult as $row) {
            $player = $row[0];

            $player->setTeam(null);
            $player->setContractSalary(null);
            $player->setContractYears(null);
            $player->setJerseyNumber(null);
            $player->setSeasonEndsContract(null);

            if (($i % self::BATCH_SIZE) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear(Player::class);
            }

            ++$i;
        }

        $this->entityManager->flush();
    }
}
