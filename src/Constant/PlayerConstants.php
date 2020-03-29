<?php declare(strict_types=1);

namespace App\Constant;

use App\Entity\GameType;
use App\Entity\Position;

class PlayerConstants
{
    const MIN_HEIGHT = 'minHeight';
    const MAX_HEIGHT = 'maxHeight';
    const MIN_WEIGHT = 'minWeight';
    const MAX_WEIGHT = 'maxWeight';

    const PLAYER_MAX_MIN_WEIGHTS_AND_HEIGHTS_BY_POSITION = [
        Position::POINT_GUARD => [
            self::MIN_HEIGHT => 170,
            self::MAX_HEIGHT => 190,
            self::MIN_WEIGHT => 60,
            self::MAX_WEIGHT => 80
        ],
        Position::SHOOTING_GUARD => [
            self::MIN_HEIGHT => 188,
            self::MAX_HEIGHT => 201,
            self::MIN_WEIGHT => 60,
            self::MAX_WEIGHT => 80
        ],
        Position::SMALL_FORWARD => [
            self::MIN_HEIGHT => 198,
            self::MAX_HEIGHT => 206,
            self::MIN_WEIGHT => 80,
            self::MAX_WEIGHT => 100
        ],
        Position::POWER_FORWARD => [
            self::MIN_HEIGHT => 201,
            self::MAX_HEIGHT => 213,
            self::MIN_WEIGHT => 80,
            self::MAX_WEIGHT => 110
        ],
        Position::CENTER => [
            self::MIN_HEIGHT => 203,
            self::MAX_HEIGHT => 216,
            self::MIN_WEIGHT => 90,
            self::MAX_WEIGHT => 120
        ]
    ];

    const VALID_GAME_TYPES = [
        GameType::TYPE_SCORING,
        GameType::TYPE_ASSISTING,
        GameType::TYPE_REBOUNDING,
        GameType::TYPE_STEALING,
        GameType::TYPE_BLOCKING
    ];
}