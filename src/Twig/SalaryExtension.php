<?php


namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class SalaryExtension
 *
 * @package App\Twig
 */
class SalaryExtension extends AbstractExtension
{
    /**
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('salary', [$this, 'formatSalary']),
        ];
    }

    /**
     * @param float $salary
     *
     * @return string
     */
    public function formatSalary(float $salary): string
    {
        return '$' . round($salary, 2) . ' / a game';
    }
}
