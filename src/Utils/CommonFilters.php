<?php

namespace App\Utils;


class CommonFilters
{

    /**
     * @param string $value
     *
     * @return string|string[]
     */
    public static function sanitizeCacheKey($value)
    {
        return str_replace(['/', ':'], ['M', '-'], $value);
    }

}
