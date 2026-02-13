<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $host = request()->getHost();
        $isNgrokHost = str_contains($host, 'ngrok');
        $isLocalHost = in_array($host, ['localhost', '127.0.0.1'], true);

        if (str_starts_with((string) config('app.url'), 'https://') && $isNgrokHost && !$isLocalHost) {
            URL::forceScheme('https');
        }
    }
}
