<?php

namespace Mrluke\Searcher\Tests;

use Mrluke\Searcher\Searcher;
use Orchestra\Testbench\TestCase as BaseCase;

/**
 * TestsBase - phpunit master file for this package.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 *
 * @link      http://github.com/mr-luke/searcher
 *
 * @category  Laravel
 *
 * @license   MIT
 */
class TestCase extends BaseCase
{
    /**
     * DB configuration.
     */
    const DB_HOST = 'localhost';
    const DB_NAME = 'packages';
    const DB_USERNAME = 'root';
    const DB_PASSWORD = 'root';
    const DB_PREFIX = 'searcher_';

    /**
     * Setup TestCase.
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->makeSureDatabaseExists(static::DB_NAME);

        parent::setUp();

        $this->loadMigrationsFrom([
            '--database' => 'mysql',
            '--realpath' => realpath(__DIR__.'/database/migrations'),
        ]);

        $this->withFactories(__DIR__.'/database/factories');

        $this->refreshSeedData();
    }

    /**
     * Get application timezone.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return string|null
     */
    protected function getApplicationTimezone($app)
    {
        return 'Europe/Warsaw';
    }

    /**
     * Seting enviroment for Test.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app) : void
    {
        $app['path.base'] = __DIR__.'/..';
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver'    => 'mysql',
            'host'      => static::DB_HOST,
            'database'  => static::DB_NAME,
            'username'  => static::DB_USERNAME,
            'password'  => static::DB_PASSWORD,
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'strict'    => true,
            'prefix'    => static::DB_PREFIX,
        ]);
        $app['config']->set('app.faker_locale', 'pl_PL');
    }

    /**
     * Return array of providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app) : array
    {
        return [
            \Mrluke\Searcher\SearcherServiceProvider::class,
        ];
    }

    /**
     * Create database if not exists.
     *
     * @param string $dbName
     *
     * @return void
     */
    private function makeSureDatabaseExists(string $dbName) :void
    {
        $this->runQuery('CREATE DATABASE IF NOT EXISTS '.$dbName);
    }

    /**
     * Peroform seeding.
     *
     * @return void
     */
    private function refreshSeedData() : void
    {
        $this->truncateAllTablesButMigrations(static::DB_NAME);
        $seeder = new \DataSeeder();
        $seeder->run();
    }

    /**
     * Run Query.
     *
     * @param string $query
     *
     * @return void
     */
    private function runQuery(string $query) : void
    {
        $dbUsername = static::DB_USERNAME;
        $dbPassword = static::DB_PASSWORD;
        $command = "mysql -u $dbUsername ";
        $command .= $dbPassword ? " -p$dbPassword" : '';
        $command .= " -e '$query'";
        exec($command.' 2>/dev/null');
    }

    /**
     * Truncate each table except migrations.
     *
     * @param string $dbName
     *
     * @return void
     */
    private function truncateAllTablesButMigrations(string $dbName) : void
    {
        $db = $this->app->make('db');
        $db->statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($tables = $db->select('SHOW TABLES') as $table) {
            $table = $table->{'Tables_in_'.$dbName};
            $table = str_replace(static::DB_PREFIX, '', $table);
            if ($table != 'migrations') {
                $db->table($table)->truncate();
            }
        }
        $db->statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
