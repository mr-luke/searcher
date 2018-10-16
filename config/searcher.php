<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API mode
    |--------------------------------------------------------------------------
    |
    | API mode determine if pagination should be Laravel's LenghtAwarePagination
    | instance or classical offset & limit REST pagination.
    |
    */

    'api_mode' => false,

    /*
    |--------------------------------------------------------------------------
    | Allows options
    |--------------------------------------------------------------------------
    |
    | These configurations allow you to turn on/off some parts of package like:
    | quering, filtering, or sorting. Feel free to set them up as you like.
    | These are global configs, so you can override them by using setOptions()
    | method.
    |
    */

    'allow_filter' => true,
    'allow_query' => true,
    'allow_sort' => true,

    /*
    |--------------------------------------------------------------------------
    | Pagination settings
    |--------------------------------------------------------------------------
    |
    | These configurations determine if auto-pagination by request inputs is
    | allowed and limit for per page items amount.
    |
    */

    'auto_pagination' => false,
    'limit' => 20,

];
