<?php

namespace Mrluke\Searcher\Traits;

/**
 * This trait allows to simplify configuration.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 *
 * @link      http://github.com/mr-luke/searcher
 *
 * @category  Laravel
 *
 * @license   MIT
 */
trait Searchable
{
    /**
     * Searcher configuration.
     *
     * @var array
     */
    protected static $searchableConfig = [];

    /**
     * Determines rules for Searcher.
     *
     * @return array
     */
    public static function getSearchableConfig() : array
    {
        return self::$searchableConfig;
    }
}
