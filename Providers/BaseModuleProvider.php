<?php namespace Cms\Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Pingpong\Modules\Module;

class BaseModuleProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerMiddleware($this->app['router']);
        $this->registerModuleCommands();
        $this->registerModuleResourceNamespaces();
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

    /**
     * Register the middleware.
     *
     * @param  Router $router
     * @return void
     */
    public function registerMiddleware(Router $router)
    {
        if (!count($this->middleware)) {
            return;
        }

        foreach ($this->middleware as $module => $middlewares) {
            if (!count($middlewares)) {
                continue;
            }
            foreach ($middlewares as $name => $middleware) {
                $class = sprintf('Cms\Modules\%s\Http\Middleware\%s', $module, $middleware);
                $router->middleware($name, $class);
            }
        }
    }

    /**
     * Register the commands.
     */
    private function registerModuleCommands()
    {
        if (!count($this->commands)) {
            return;
        }

        foreach ($this->commands as $module => $commands) {
            if (!count($commands)) {
                continue;
            }

            foreach ($commands as $command => $class) {
                $this->app[$command] = $this->app->share(function () use ($module, $class) {
                    $class = sprintf('Cms\Modules\%s\Console\%s', $module, $class);
                    return new $class($this->app);
                });
                $this->commands($command);
            }
        }
    }


    /**
     * Register the modules aliases
     */
    private function registerModuleResourceNamespaces()
    {
        foreach ($this->app['modules']->enabled() as $module) {
            $this->registerViewNamespace($module);
            $this->registerLanguageNamespace($module);
            $this->registerConfigNamespace($module);
        }
    }

    /**
     * Register the view namespaces for the modules
     * @param Module $module
     */
    protected function registerViewNamespace(Module $module)
    {
        $this->app['view']->addNamespace(
            $module->getName(),
            $module->getPath() . '/Resources/views'
        );
    }

    /**
     * Register the language namespaces for the modules
     * @param Module $module
     */
    protected function registerLanguageNamespace(Module $module)
    {
        $this->app['translator']->addNamespace(
            $module->getName(),
            $module->getPath() . '/Resources/lang'
        );
    }

    /**
     * Register the config namespace
     * @param Module $module
     */
    private function registerConfigNamespace(Module $module)
    {
        $files = $this->app['files']->files($module->getPath() . '/Config');

        $package = $module->getName();

        foreach ($files as $file) {
            $filename = $this->getConfigFilename($file, $package);

            $this->mergeConfigFrom(
                $file,
                $filename
            );

            $this->publishes([
                $file => config_path($filename . '.php'),
            ], 'config');
        }
    }

    /**
     * @param $file
     * @param $package
     * @return string
     */
    private function getConfigFilename($file, $package)
    {
        $name = preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($file));

        return sprintf('modules.%s.%s', $package, $name);
    }
}