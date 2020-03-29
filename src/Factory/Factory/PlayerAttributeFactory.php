<?php declare(strict_types=1);

namespace App\Factory\Factory;

use App\Entity\Player;
use App\Factory\Model\PlayerAttributeModel;
use App\Utils\PlayerAttribute;

class PlayerAttributeFactory
{
    /**
     * @var PlayerAttribute
     */
    private $playerAttributeUtil;

    /**
     * PlayerAttributeFactory constructor.
     *
     * @param PlayerAttribute $playerAttributeUtil
     */
    public function __construct(PlayerAttribute $playerAttributeUtil)
    {
        $this->playerAttributeUtil = $playerAttributeUtil;
    }

    /**
     * @param array  $attributes
     * @param Player $player
     * @param bool   $userPlayer
     *
     * @return array
     *
     * @throws \Exception
     */
    public function create(array $attributes, Player $player, bool $userPlayer = false): array
    {
        $result = [];

        foreach($attributes as $attribute) {
            $result[] = new PlayerAttributeModel(
                $attribute['attributeId'],
                $attribute['attributeName'],
                $this->playerAttributeUtil->formatScore($attribute['attributeLevel']),
                $attribute['attributeLevel'],
                $userPlayer ? $this->playerAttributeUtil->getPlayerAttributeImprovePrice(
                    $player,
                    $attribute['gameTypeName'],
                    $attribute['attributeLevel']
                ) : null
            );
        }

        return $result;
    }
}