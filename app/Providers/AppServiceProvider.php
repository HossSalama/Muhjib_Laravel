<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use App\Models\Client;
use App\Observers\ClientObserver;
use App\Models\Product;
use App\Observers\ProductObserver;
use App\Models\Template;
use App\Observers\TemplateObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Client::observe(ClientObserver::class);
        Product::observe(ProductObserver::class);
        Template::observe(TemplateObserver::class);

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        $publicStorage = public_path('storage');
        $target = storage_path('app/public');

        if (!file_exists($publicStorage)) {
            app('files')->link($target, $publicStorage);
        }
    }
}
