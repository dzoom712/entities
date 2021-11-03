<?php

namespace dongxiannan\entities;

use dongxiannan\entities\Commands\ModelMakeCommand;
use Illuminate\Support\ServiceProvider;

class EntitiesProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([
            ModelMakeCommand::class
        ]);
    }
}
