<?php

namespace Beesofts\Hydrator;

/**
 * Credit to carousel https://gist.github.com/carousel/1aacbea013d230768b3dec1a14ce5751.
 */
class CaseConverter
{
    public static function camelToSnake(string $input): string
    {
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    public static function snakeToCamel(string $input): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }
}
