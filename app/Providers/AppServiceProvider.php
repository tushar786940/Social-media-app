<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

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
        // Share common data with all views
        View::share([
            'appName' => config('app.name', 'Social'),
            'currentYear' => date('Y'),
        ]);

        // Register Blade directives
        Blade::directive('datetime', function ($expression) {
            return "<?php echo ($expression)->format('Y-m-d H:i:s'); ?>";
        });

        Blade::directive('date', function ($expression) {
            return "<?php echo ($expression)->format('Y-m-d'); ?>";
        });

        Blade::directive('diffForHumans', function ($expression) {
            return "<?php echo ($expression)->diffForHumans(); ?>";
        });

        Blade::directive('formatNumber', function ($expression) {
            return "<?php echo number_format($expression); ?>";
        });

        Blade::directive('strLimit', function ($expression) {
            return "<?php echo \Illuminate\\Support\\Str::limit($expression); ?>";
        });

        Blade::directive('assetVersion', function ($expression) {
            return "<?php echo asset($expression) . '?v=' . filemtime(public_path($expression)); ?>";
        });
    }
}
