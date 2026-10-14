<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;

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
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer')
                );
            });

        Gate::before(function ($user, $ability) {
            $registrar = app(PermissionRegistrar::class);

            $current = $registrar->getPermissionsTeamId();
            $registrar->setPermissionsTeamId(0);

            $isSuper = $user->hasRole('superadmin', 'sanctum');

            $registrar->setPermissionsTeamId($current);

            return $isSuper ? true : null;
        });
    }
}
