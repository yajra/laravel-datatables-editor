<?php

namespace Yajra\DataTables\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Yajra\DataTables\EditorServiceProvider;
use Yajra\DataTables\Tests\Editors\UsersDataTableEditor;
use Yajra\DataTables\Tests\Editors\UsersWithEventsDataTableEditor;
use Yajra\DataTables\Tests\Models\User;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateDatabase();

        $this->app['router']->post('users', fn (UsersDataTableEditor $editor, Request $request) => $editor->process($request));

        $this->app['router']->post('usersWithEvents', fn (UsersWithEventsDataTableEditor $editor, Request $request) => $editor->process($request));
    }

    protected function migrateDatabase()
    {
        /** @var \Illuminate\Database\Schema\Builder $schemaBuilder */
        $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();
        $schemaBuilder->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('password')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Set up the environment.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', true);
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [EditorServiceProvider::class];
    }

    protected function createUser($attributes = [])
    {
        if (! $attributes) {
            $attributes = [
                'name' => 'Taylor',
                'email' => 'taylor@laravel.com',
            ];
        }

        return User::query()->forceCreate($attributes);
    }
}
