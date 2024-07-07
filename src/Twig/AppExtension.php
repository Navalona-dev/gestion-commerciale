<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_date', [$this, 'formatDate']),
        ];
    }

    public function formatDate($createdAt): string
    {
        $now = new \DateTime();
        $diff = $now->diff($createdAt);

        if ($diff->y > 0) {
            return $createdAt->format('j M Y');
        } elseif ($diff->m > 0 || $diff->d > 1) {
            return $createdAt->format('j M');
        } elseif ($diff->d === 1) {
            return 'Hier';
        } elseif ($diff->h > 0) {
            return 'il y a ' . $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        } elseif ($diff->i > 0) {
            return 'il y a ' . $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        } else {
            return 'À l\'instant';
        }
    }
}
