<?php declare(strict_types=1);

namespace App\Factory\Factory;

use App\Entity\Player;
use App\Factory\Model\PlayerDetailsModel;
use App\Utils\PlayerAttribute;

class PlayerDetailsFactory
{
    /**
     * @var PlayerAttribute
     */
    private $playerAttributeUtil;

    /**
     * PlayerDetailsFactory constructor.
     *
     * @param PlayerAttribute $playerAttributeUtil
     */
    public function __construct(PlayerAttribute $playerAttributeUtil)
    {
        $this->playerAttributeUtil = $playerAttributeUtil;
    }

    /**
     * @param Player $player
     *
     * @return PlayerDetailsModel
     */
    public function create(Player $player): PlayerDetailsModel
    {
        return new PlayerDetailsModel(
            $player->getMoney(),
            $this->playerAttributeUtil->formatScore($player->getPlayerRating()),
            $player->getChampionRings(),
            $player->getDPOYs(),
            $player->getMVPs()
        );
    }
}