<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FrenchToDateTimeTransformer implements DataTransformerInterface
{
    public function transform($datetime)
    {
        if (!is_object($datetime) || !($datetime instanceof \DateTimeInterface)) {
            return '';
        }

        return $datetime->format('d/m/Y');
    }

    public function reverseTransform($frenchDatetime)
    {
        if ($frenchDatetime === null || $frenchDatetime === '') {
            // Retourner null si la date est vide ou null
            return null;
        }

        $datetime = \DateTime::createFromFormat('d/m/Y', $frenchDatetime);

        if ($datetime === false) {
            throw new TransformationFailedException("Le format de la date n'est pas le bon!");
        }

        return $datetime;
    }
}
