<?php

namespace App\Model;

use App\Entity\GameType;
use App\Entity\Player;

/**
 * Class PlayerScore
 *
 * @package App\Model
 */
class PlayerScore
{
    /**
     * @var Player
     */
    protected $player;

    /**
     * @var int
     */
    protected $points;

    /**
     * @var int
     */
    protected $rebounds;

    /**
     * @var int
     */
    protected $assists;

    /**
     * @var int
     */
    protected $steals;

    /**
     * @var int
     */
    protected $blocks;

    /**
     * @var GameType $gameTypeOne
     */
    protected $gameTypeOne;

    /**
     * @var GameType $gameTypeTwo
     */
    protected $gameTypeTwo;

    /**
     * PlayerScore constructor.
     *
     * @param Player $player
     */
    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param Player $player
     */
    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    /**
     * @return int
     */
    public function getPoints(): int
    {
        return $this->points;
    }

    /**
     * @param int $points
     */
    public function setPoints(int $points): void
    {
        $this->points = $points;
    }

    /**
     * @return int
     */
    public function getRebounds(): int
    {
        return $this->rebounds;
    }

    /**
     * @param int $rebounds
     */
    public function setRebounds(int $rebounds): void
    {
        $this->rebounds = $rebounds;
    }

    /**
     * @return int
     */
    public function getAssists(): int
    {
        return $this->assists;
    }

    /**
     * @param int $assists
     */
    public function setAssists(int $assists): void
    {
        $this->assists = $assists;
    }

    /**
     * @return int
     */
    public function getSteals(): int
    {
        return $this->steals;
    }

    /**
     * @param int $steals
     */
    public function setSteals(int $steals): void
    {
        $this->steals = $steals;
    }

    /**
     * @return int
     */
    public function getBlocks(): int
    {
        return $this->blocks;
    }

    /**
     * @param int $blocks
     */
    public function setBlocks(int $blocks): void
    {
        $this->blocks = $blocks;
    }

    /**
     * @return GameType
     */
    public function getGameTypeOne(): GameType
    {
        return $this->gameTypeOne;
    }

    /**
     * @param GameType $gameTypeOne
     */
    public function setGameTypeOne(GameType $gameTypeOne): void
    {
        $this->gameTypeOne = $gameTypeOne;
    }

    /**
     * @return GameType
     */
    public function getGameTypeTwo(): GameType
    {
        return $this->gameTypeTwo;
    }

    /**
     * @param GameType $gameTypeTwo
     */
    public function setGameTypeTwo(GameType $gameTypeTwo): void
    {
        $this->gameTypeTwo = $gameTypeTwo;
    }
}
