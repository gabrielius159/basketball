<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Season;
use Symfony\Contracts\EventDispatcher\Event;

class GenerateScheduleEvent extends Event
{
    const NAME = 'generate_schedule';

    private $season;

    /**
     * GenerateScheduleEvent constructor.
     *
     * @param Season $season
     */
    public function __construct(Season $season)
    {
        $this->season = $season;
    }

    /**
     * @return Season
     */
    public function getSeason(): Season
    {
        return $this->season;
    }
}