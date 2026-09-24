<?php

namespace App\Providers;

use App\Services\FooterContentService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return url(route('console.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
        });

        View::composer('layout.frontend', function ($view) {
            $view->with('siteFooter', app(FooterContentService::class)->forFrontend());
        });
    }
}
