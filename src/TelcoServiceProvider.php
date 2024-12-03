<?php

namespace Aislandener\Telco;

use Aislandener\Telco\Services\TelcoService;
use Illuminate\Support\ServiceProvider;

class TelcoServiceProvider extends ServiceProvider
{
    public function register()
    {
        $configPath = __DIR__.'/../config/telco.php';
        $this->mergeConfigFrom($configPath, 'telco');
        $this->injectDependencies();

        $this->app->singleton('Telco', fn () => resolve(TelcoService::class));

    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfig();
        }

    }

    private function injectDependencies(): void
    {
        $this->app->when(TelcoService::class)->needs('$url')->giveConfig('telco.url');
        $this->app->when(TelcoService::class)->needs('$username')->giveConfig('telco.username');
        $this->app->when(TelcoService::class)->needs('$password')->giveConfig('telco.password');
        $this->app->when(TelcoService::class)->needs('$recurrenceKey')->giveConfig('telco.recurrence.key');
        $this->app->when(TelcoService::class)->needs('$recurrenceCipher')->giveConfig('telco.recurrence.cipher');
    }

    private function publishConfig(): void
    {
        $configPath = __DIR__.'/../config/telco.php';
        if (function_exists('config_path')) {
            $publishPath = config_path('telco.php');
        } else {
            $publishPath = base_path('config/telco.php');
        }

        $this->publishes([
            $configPath => $publishPath,
        ], ['telco-config']);
    }
}
