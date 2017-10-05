<?php

namespace Admin\Providers;

use Illuminate\Support\Facades\Route;
use App\Providers\BaseRouteServiceProvider;

class RouteServiceProvider extends BaseRouteServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Admin\Http\Controllers';
    protected $prefix;

    public function __construct($app) {
        parent::__construct($app);
        $this->prefix = config('admin.prefix', 'admin');
    }

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::prefix($this->locale . '/' . $this->prefix)
             ->middleware('web')
             ->namespace($this->namespace)
             ->name('admin::')
             ->group(__DIR__.'/../../routes/web.php');
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix($this->locale . '/'. $this->prefix . '/api')
             ->middleware('api')
             ->namespace($this->namespace . '\\Api')
             ->name('admin::api.')
             ->group(__DIR__.'/../../routes/api.php');
    }
}

