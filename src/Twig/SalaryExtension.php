<?php declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

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
