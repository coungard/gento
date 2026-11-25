<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Page;
use App\Services\QuoteCart;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        RateLimiter::for('quote', fn (Request $request) => Limit::perMinute(6)->by($request->ip()));
        RateLimiter::for('lead', fn (Request $request) => Limit::perMinute(6)->by($request->ip()));

        View::composer('layouts.app', function (ViewContract $view): void {
            $view->with([
                'navCategories' => Category::query()
                    ->active()
                    ->roots()
                    ->ordered()
                    ->with(['children' => fn ($query) => $query->active()->ordered()])
                    ->get(),
                'navPages' => Page::query()
                    ->active()
                    ->orderBy('sort')
                    ->get(),
                'quoteCount' => app(QuoteCart::class)->positions(),
            ]);
        });
    }
}
