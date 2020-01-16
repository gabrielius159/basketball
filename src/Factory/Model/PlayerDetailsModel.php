<?php declare(strict_types=1);

namespace App\Factory\Model;

use JMS\Serializer\Serializer AS Serializer;

class PlayerDetailsModel
{
    /**
     * @var float
     *
     * @Serializer\Type(name="float")
     */
    public $money;

    /**
     * @var string
     *
     * @Serializer\Type(name="string")
     */
    public $playerRating;

    /**
     * @var int
     *
     * @Serializer\Type(name="integer")
     */
    public $championRings;

    /**
     * @var int
     *
     * @Serializer\Type(name="integer")
     */
    public $dpoyAwards;

    /**
     * @var int
     *
     * @Serializer\Type(name="integer")
     */
    public $mvpAwards;

    /**
     * PlayerDetailsModel constructor.
     *
     * @param float $money
     * @param string $playerRating
     * @param int $championRings
     * @param int $dpoyAwards
     * @param int $mvpAwards
     */
    public function __construct(float $money, string $playerRating, int $championRings, int $dpoyAwards, int $mvpAwards)
    {
        $this->money = $money;
        $this->playerRating = $playerRating;
        $this->championRings = $championRings;
        $this->dpoyAwards = $dpoyAwards;
        $this->mvpAwards = $mvpAwards;
    }
}