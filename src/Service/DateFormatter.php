<?php
// src/Service/DateFormatter.php
namespace App\Service;

use DateTime;

class DateFormatter
{
    public function formatDate(DateTime $createdAt): string
    {
        $now = new DateTime();
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
