<?php

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
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * CheckContractsHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param PlayerRepository $playerRepository
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
        $server = $this->entityManager->getRepository(Server::class)->find($message->getServerId());
        $players = $this->playerRepository->getRealPlayersWithExpiringContract($server, $message->getSeasonId());

        if($playerNumber = count($players) > 0) {
            $numberOfChunks = intval(ceil($playerNumber / 1000));
            $chunks = array_chunk($players, $numberOfChunks);

            foreach($chunks as $chunk) {
                /**
                 * @var Player $player
                 */
                foreach($chunk as $player) {
                    $player->setTeam(null);
                    $player->setContractSalary(null);
                    $player->setContractYears(null);
                    $player->setJerseyNumber(null);
                    $player->setSeasonEndsContract(null);
                }

                $this->entityManager->flush();
            }
        }

        $this->entityManager->clear();
    }
}
