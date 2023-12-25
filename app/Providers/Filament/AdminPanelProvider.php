<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use App\Http\Middleware;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use App\Filament\AvatarProviders\BoringAvatarsProvider;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Support\Enums\MaxWidth;
use App\Filament\Resources\ProductResource\Widgets\ProductTableStatsOverview;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/')
            ->login()
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                // ProductTableStatsOverview::class
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                // Middleware\RedirectIfNotFilamentAdmin::class
            ])
            ->defaultAvatarProvider(BoringAvatarsProvider::class)
            ->passwordReset()
            ->emailVerification()
            ->profile()
            ->font('Roboto', provider: GoogleFontProvider::class)
            ->brandName('Green Market')
            ->favicon(asset('images/favicon.png'))
            ->darkMode(false)
            ->sidebarFullyCollapsibleOnDesktop()
            ->maxContentWidth('max-w-full')
            ->sidebarWidth('17rem')
            ->navigationItems([
                NavigationItem::make('Trang Web')
                    ->url('http://localhost:3000', shouldOpenInNewTab: true)
                    ->icon('heroicon-s-globe-asia-australia')
                    ->group('Liên kết ngoài')
                    ->sort(1)
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Cài đặt')
                    ->url('admin/setting')
                    ->icon('heroicon-s-cog-8-tooth')
            ])
            ;
    }
}
