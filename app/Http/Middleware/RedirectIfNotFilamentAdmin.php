<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class RedirectIfNotFilamentAdmin extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    protected function authenticate($request, array $guards)
    {
        $auth = Filament::auth();

        if (!$auth->check()) {
            $this->unauthenticated($request, $guards);

            return;
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        /** @var Model $user */
        $user = $auth->user();

        $panel = Filament::getCurrentPanel();

        if ($user instanceof FilamentUser) {
            // dd(route('filament.admin.auth.login'));
            if (!$user->canAccessPanel($panel)) {
                return redirect(route('filament.admin.auth.login'));
            }
        }
    }

    protected function redirectTo($request): ?string
    {
          return Filament::getLoginUrl();
    }
}
