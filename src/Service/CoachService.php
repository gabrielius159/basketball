<?php

namespace App\Service;

use App\Entity\Coach;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class CoachService
 *
 * @package App\Service
 */
class CoachService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * CoachService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $id
     *
     * @return Coach|null
     */
    public function findOneById(int $id)
    {
        /**
         * @var Coach $coach
         */
        $coach = $this->entityManager->getRepository(Coach::class)->find($id);

        return $coach;
    }

}
