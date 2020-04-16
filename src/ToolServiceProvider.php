<?php

namespace Armincms\SofreApi;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Armincms\SofreApi\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    { 
        $this->routes();   
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    { 
        Route::middleware([])
                ->prefix('api/v1')
                ->namespace(__NAMESPACE__.'\\Http\\Controllers')
                ->name('sofre.api.')
                ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
