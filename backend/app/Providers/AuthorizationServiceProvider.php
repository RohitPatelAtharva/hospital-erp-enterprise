<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Registers a Gate for every canonical permission.
 *
 * Endpoints authorize via Gate::authorize(Permissions::X) or policies, so
 * permissions are enforced centrally (least privilege, default deny) rather
 * than scattered as inline strings. The permission catalog (app/Support/
 * Permissions.php) mirrors docs/modules/master-data/11-Permissions.md.
 */
final class AuthorizationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach (Permissions::all() as $permission) {
            Gate::define($permission, static fn (User $user): bool => $user->hasPermission($permission));
        }
    }
}
