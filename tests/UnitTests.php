<?php

namespace Mrluke\Searcher\Tests;

use Mrluke\Searcher\Tests\TestCase;

use Mrluke\Searcher\Contract\Searchable;
use Mrluke\Searcher\Facades\Searcher;

use Mrluke\Searcher\Tests\Models\Post;
use Mrluke\Searcher\Tests\Models\User;

/**
 * UnitTests for package.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/searcher
 *
 * @category  Laravel
 * @package   mr-luke/searcher
 * @license   MIT
 */
class UnitTests extends TestCase
{

    public function testFacadeReturnSearcherInstance()
    {
        $searcher = Searcher::setQuery([]);

        $this->assertEquals(
            \Mrluke\Searcher\Searcher::class,
            get_class($searcher)
        );
    }

    public function testThrowsExceptionForNullBuilder()
    {
        $this->expectException(\InvalidArgumentException::class);

        Searcher::setModel(['sort' => '']);
    }

    public function testThrowsExceptionForNotBuilder()
    {
        $this->expectException(\TypeError::class);

        $user = User::first();

        Searcher::setModel(User::class, $user->posts());
    }

    public function testGetBuilderReturnBuilderInstnace()
    {
        $return = Searcher::setModel(User::class)->getBuilder();

        $this->assertEquals(
            \Illuminate\Database\Eloquent\Builder::class,
            get_class($return)
        );
    }
}
