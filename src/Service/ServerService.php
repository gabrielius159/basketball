<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Server;
use Doctrine\ORM\EntityManagerInterface;

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
    public function getCurrentServer(): ?Server
    {
        return $this->entityManager->getRepository(Server::class)->findOneBy([
            'name' => Server::SERVER_ONE
        ]);
    }
}
