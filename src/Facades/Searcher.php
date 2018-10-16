<?php

namespace Mrluke\Searcher\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Laravel's facade file for package Searcher.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/searcher
 *
 * @category  Laravel
 * @package   mr-luke/searcher
 * @license   MIT
 */
class Searcher extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mrluke-searcher';
    }
}
