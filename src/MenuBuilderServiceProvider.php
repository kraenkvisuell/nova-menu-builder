<?php

namespace KraenkVisuell\MenuBuilder;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use KraenkVisuell\MenuBuilder\Commands\CreateMenuItemType;
use KraenkVisuell\MenuBuilder\Http\Middleware\Authorize;
use Laravel\Nova\Nova;

class MenuBuilderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-menu');

        // Load migrations
        if (config('nova-menu.auto_load_migrations', true)) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        // Publish data
        $this->publishes([__DIR__.'/../database/migrations' => database_path('migrations')], 'nova-menu-builder-migrations');
        $this->publishes([__DIR__.'/../config' => config_path()], 'nova-menu-builder-config');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateMenuItemType::class,
            ]);
        }

        // Register resource
        Nova::resources([
            MenuBuilder::getMenuResource(),
        ]);

        // Register routes
        $this->app->booted(function () {
            $this->routes();
        });

        Validator::extend('unique_menu', function ($attribute, $value, $parameters, $validator) {
            // Check if menu has unique attribute defined.
            $uniqueParams = implode(',', $parameters);

            return (MenuBuilder::getMenus()[$value]['unique'] ?? true)
                // If unique attribute is true or not defined, call unique validator
                ? Validator::make([$attribute => $value], ['slug' => "unique:$uniqueParams"])->validate()
                : true;
        }, '');
    }

    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
            ->namespace('KraenkVisuell\MenuBuilder\Http\Controllers')
            ->prefix('nova-vendor/nova-menu')
            ->group(__DIR__.'/../routes/api.php');
    }
}
