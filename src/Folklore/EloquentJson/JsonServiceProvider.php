<?php namespace Folklore\EloquentJson;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Folklore\EloquentJson\JsonSchemaValidator;

class JsonServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the package
     *
     * @return void
     */
    public function boot()
    {
        $this->bootPublishes();

        $this->bootValidator();
    }

    /**
     * Bootstrap the package publications
     *
     * @return void
     */
    protected function bootPublishes()
    {
        // Config file path
        $configPath = __DIR__ . '/../../config/config.php';

        // Merge files
        $this->mergeConfigFrom($configPath, 'eloquent-json');

        // Publish
        $this->publishes(
            [
                $configPath => config_path('eloquent-json.php')
            ],
            'config'
        );
    }

    /**
     * Bootstrap the json schema validator
     *
     * @return void
     */
    protected function bootValidator()
    {
        $this->app['validator']->extend(
            'json_schema',
            \Folklore\EloquentJson\Contracts\JsonValidator::class . '@validate'
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerValidator();
    }

    /**
     * Register the json schema validator
     *
     * @return void
     */
    protected function registerValidator()
    {
        $this->app->singleton('eloquent-json.validator', function ($app) {
            return new JsonSchemaValidator($app);
        });

        $this->app->bind(
            \Folklore\EloquentJson\Contracts\JsonSchemaValidator::class,
            'eloquent-json.validator'
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
