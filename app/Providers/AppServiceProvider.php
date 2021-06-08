<?php

namespace App\Providers;

use App\Collaborators\ApiClient;
use Illuminate\Support\ServiceProvider;
use App\Collaborators\Adapters\GuzzleAdapter;
use JSHayes\FakeRequests\ClientFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\Libraries\iJWTLibrary',
            'App\Libraries\FirebaseJWTLibrary'
        );

        $this->app->bind('api-client', function ($app) {
            return new ApiClient(
                new GuzzleAdapter($app->make(ClientFactory::class)->make())
            );
        });
    }
}
