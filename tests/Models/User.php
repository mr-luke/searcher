<?php

namespace Mrluke\Searcher\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mrluke\Searcher\Contracts\Searchable;

class User extends Model implements Searchable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Return related model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts() : HasMany
    {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSearchableConfig() : array
    {
        return [];
    }
}
