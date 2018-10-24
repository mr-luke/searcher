<?php

namespace Mrluke\Searcher\Contracts;

/**
 * Determine if Model is searchable.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 *
 * @link      http://github.com/mr-luke/searcher
 *
 * @category  Laravel
 *
 * @license   MIT
 */
interface Searchable
{
    /**
     * Determines rules for Searcher.
     *
     * @return array
     */
    public static function getSearchableConfig() : array;
}
