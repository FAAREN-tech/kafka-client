<?php
namespace FaarenTech\KafkaClient;

use FaarenTech\KafkaClient\Consumer\Consumer;
use FaarenTech\KafkaClient\Producer\Producer;
use Illuminate\Support\ServiceProvider;

class KafkaClientServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/faaren-kafka.php' => config_path('faaren-kafka.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('producer', function($app) {
           return new Producer(config('faaren-kafka'));
        });

        $this->app->bind('consumer', function($app) {
            return new Consumer(config('faaren-kafka'));
        });
    }
}