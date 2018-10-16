<?php

namespace Mrluke\Searcher\Tests;

use Mrluke\Searcher\Tests\TestCase;

use Mrluke\Searcher\Contract\Searchable;
use Mrluke\Searcher\Facades\Searcher;

use Mrluke\Searcher\Tests\Models\Post;
use Mrluke\Searcher\Tests\Models\User;

/**
 * FeatureTests for package.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/searcher
 *
 * @category  Laravel
 * @package   mr-luke/searcher
 * @license   MIT
 */
class FeatureTests extends TestCase
{
    public function testQueryMapAsExpected()
    {
        $inputs = [
            'first' => 'jan,ne anna',
            'age' => 'gt 30',
            'sort' => '+first',
        ];

        $expect = [[
            "type" => "whereNested",
            "insert" => [
                [
                    "method" => "where",
                    "field" => "first",
                    "op" => "=",
                    "val" => "jan",
                    "bool" => 'and',
                ],[
                    "method" => "where",
                    "field" => "first",
                    "op" => "<>",
                    "val" => "anna",
                    "bool" => 'and',
                ]
            ]],[
            "type" => "whereNested",
            "insert" => [
                [
                    "method" => "where",
                    "field" => "age",
                    "op" => ">",
                    "val" => "30",
                    "bool" => 'and',
                ]
            ]],[
            "type" => "orderBy",
            "insert" => ["first", "asc"]
            ]
        ];

        $map = Searcher::setModel([
            'filter' => ['first' => 'first', 'age' => 'age'],
            'sort' => ['first' => 'first']
        ], User::query())->setQuery($inputs)->getQueryMap();

        $this->assertEquals($expect, $map);
    }

    public function testCompareEloquentToSearcher()
    {
        $inputs = [
            'first' => 'jan,ne anna',
            'age' => 'gt 30',
            'sort' => '+first',
        ];

        $users = User::where(function($q) {
            $q->where('first', 'jan')->where('first', '!=', 'anna');
        })->where('age', '>', 30)->orderBy('first')->get()->toArray();

        $searched = Searcher::setModel([
            'filter' => ['first' => 'first', 'age' => 'age'],
            'sort' => ['first' => 'first']
        ], User::query())->setQuery($inputs)->get()->toArray();

        $this->assertEquals($users, $searched);
    }
}
