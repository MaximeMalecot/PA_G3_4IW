<?php

namespace App\Service\Type;

class ArrayService
{
    public static function getRandomElem(array &$array)
    {   
        $index = rand(0, (count($array) - 1));
        $elem = $array[$index];
        unset($array[$index]);
        $array = array_values($array);
        return $elem;
    }
}