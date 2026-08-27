<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\Messaging\SmsProviderInterface;
use App\Services\Messaging\LogSmsProvider;
use App\Services\Messaging\WhatsAppProviderInterface;
use App\Services\Messaging\MetaWhatsAppProvider;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // No SMS vendor chosen yet — see config/services.php `sms` and
        // LogSmsProvider's docblock. Swap this binding once one is.
        $this->app->bind(SmsProviderInterface::class, LogSmsProvider::class);

        // MetaWhatsAppProvider self-degrades to a log-only dry run when
        // services.whatsapp isn't configured, so this binding doesn't
        // need to change once real credentials are added later.
        $this->app->bind(WhatsAppProviderInterface::class, MetaWhatsAppProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
      Schema::defaultStringLength(191);
    }
}
