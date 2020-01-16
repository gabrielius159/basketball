<?php

namespace App\Service;

use App\Entity\Server;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ServerService
 *
 * @package App\Service
 */
class ServerService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ServerService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return Server|null
     */
    public function getCurrentServer(): Server
    {
        /**
         * @var Server $server
         */
        $server = $this->entityManager->getRepository(Server::class)->findOneBy([
            'name' => Server::SERVER_ONE
        ]);

        return $server;
    }
}
