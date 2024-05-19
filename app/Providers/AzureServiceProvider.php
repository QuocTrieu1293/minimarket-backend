<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;


class AzureServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Storage::extend('azure', function ($app, $config) {
            $connectionString = "DefaultEndpointsProtocol=https;AccountName=" . $config['name'] . ";AccountKey=" . $config['key'] . ";EndpointSuffix=core.windows.net";
            $client = BlobRestProxy::createBlobService($connectionString);

            return new Filesystem(new AzureBlobStorageAdapter($client, (string)$config['container']));
        });
    }
}
