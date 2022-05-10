<?php

namespace Exolnet\ClosureTable\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function setUpDatabase(Application $app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('node_mocks', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        $app['db']->connection()->getSchemaBuilder()->create('node_mock_closures', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('ancestor_id');
            $table->unsignedBigInteger('descendant_id');
            $table->unsignedBigInteger('depth')->default(0);

            $table->foreign('ancestor_id')
                ->references('id')->on('node_mocks')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('descendant_id')
                ->references('id')->on('node_mocks')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
}
