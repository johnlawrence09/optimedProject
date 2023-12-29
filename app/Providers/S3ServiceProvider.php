<?php

namespace App\Providers;

use Aws\S3\S3Client;
use Illuminate\Support\ServiceProvider;

class S3ServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind('s3', function () {
            return new S3Client([
                'region' => config('services.aws.region'),
                'version' => 'latest',
                'credentials' => [
                    'key' => config('services.aws.key'),
                    'secret' => config('services.aws.secret'),
                ],
            ]);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
