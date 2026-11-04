<?php

namespace App\Providers;

use App\Auth\CachedUserProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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
        // Lempar exception bila ada relasi yang di-lazy-load (N+1) di luar production.
        Model::preventLazyLoading(! $this->app->isProduction());

        // Akses khusus admin: kelola pengguna, Sampah (pulihkan/hapus permanen).
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        Auth::provider('cached-eloquent', fn ($app, array $config) => new CachedUserProvider($app['hash'], $config['model']));
    }
}
