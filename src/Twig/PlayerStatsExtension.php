<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class PlayerStatsExtension
 *
 * @package App\Twig
 */
class PlayerStatsExtension extends AbstractExtension
{
    /**
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('stats', [$this, 'formatStats']),
        ];
    }

    /**
     * @param float $stat
     *
     * @return float
     */
    public function formatStats(float $stat): float
    {
        return round($stat, 1);
    }
}
