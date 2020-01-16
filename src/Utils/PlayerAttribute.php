<?php declare(strict_types=1);

namespace App\Utils;

use App\Entity\Player;

class PlayerAttribute
{
    const A_PLUS = 97;
    const A_NORMAL = 90;
    const A_MINUS = 88;

    const B_PLUS = 85;
    const B_NORMAL = 80;
    const B_MINUS = 70;

    const C_PLUS = 78;
    const C_NORMAL = 70;
    const C_MINUS = 60;

    const D_PLUS = 50;
    const D_NORMAL = 40;
    const D_MINUS = 30;

    const F_PLUS = 28;
    const F_NORMAL = 20;

    const DEFAULT_GYM_VALUE = 80;
    const CHEAPER_GYM_TYPE = 70;
    const CHEAPEST_GYM_VALUE = 60;

    /**
     * @param float $score
     *
     * @return string
     */
    public function formatScore(float $score): string
    {
        switch($score) {
            case $score >= self::F_NORMAL && $score < self::F_PLUS:
                return 'F';
            case $score >= self::F_PLUS && $score < self::D_MINUS:
                return 'F+';
            case $score >= self::D_MINUS && $score < self::D_NORMAL:
                return 'D-';
            case $score >= self::D_NORMAL && $score < self::D_PLUS:
                return 'D';
            case $score >= self::D_PLUS && $score < self::C_MINUS:
                return 'D+';
            case $score >= self::C_MINUS && $score < self::C_NORMAL:
                return 'C-';
            case $score >= self::C_NORMAL && $score < self::C_PLUS:
                return 'C';
            case $score >= self::C_PLUS && $score < self::B_MINUS:
                return 'C+';
            case $score >= self::B_MINUS && $score < self::B_NORMAL:
                return 'B-';
            case $score >= self::B_NORMAL && $score < self::B_PLUS:
                return 'B';
            case $score >= self::B_PLUS && $score < self::A_MINUS:
                return 'B+';
            case $score >= self::A_MINUS && $score < self::A_NORMAL:
                return 'A-';
            case $score >= self::A_NORMAL && $score < self::A_PLUS:
                return 'A';
            case $score >= self::B_PLUS:
                return 'A+';
        }

        return 'N/A';
    }

    /**
     * @param Player $player
     * @param string $attributeGameTypeName
     * @param float $attributeValue
     *
     * @return float
     */
    public function getPlayerAttributeImprovePrice(Player $player, string $attributeGameTypeName, float $attributeValue): float
    {
        $price = 0.0;

        switch($attributeGameTypeName) {
            case $player->getFirstType()->getName(): {
                $price = self::CHEAPEST_GYM_VALUE * $attributeValue;

                break;
            }
            case $player->getSecondType()->getName(): {
                $price = self::CHEAPER_GYM_TYPE * $attributeValue;

                break;
            }
            default: {
                $price = self::DEFAULT_GYM_VALUE * $attributeValue;

                break;
            }
        }

        return $price;
    }
}