<?php

namespace App\Helpers;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 *
 *
 *
 */
class Helpers
{


    public static function getNameOfClass(Object $objet)
    {
        $TabClass = explode("\\", get_class($objet));

        return $TabClass[count($TabClass) -1];
    }

    public static function serializeObject($objet, string $type)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        return $serializer->serialize($objet, $type, [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
    }
}
