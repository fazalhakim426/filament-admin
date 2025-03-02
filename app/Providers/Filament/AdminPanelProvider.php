<?php

namespace App\Providers\Filament;

use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CityResource;
use App\Filament\Resources\NotificationResource;
use App\Filament\Resources\OrderResource\Widgets\LatestOrders;
use App\Models\Category;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use Filament\Navigation\NavigationItem;
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()->colors([
                'primary' => "#FF4447",
                'gray' => "#6E6E6E"
            ])            
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->plugin(FilamentSpatieRolesPermissionsPlugin::make())
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class
            ]) 
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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
            ])->navigationItems([
                NavigationItem::make('Customer')
                    ->url('/api/documentation', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-document-text')
                    ->group('Api Documentation')
                    ->sort(10),
                NavigationItem::make('Supplier')
                ->url('/api/documentation', shouldOpenInNewTab: true)
                ->icon('heroicon-o-document-text')
                ->group('Api Documentation')
                ->sort(10)
            ]);
    }


    public static function navigation(): array
    {
        return [
            NavigationGroup::make('Settings')
                ->icon('heroicon-o-light-bulb')
                ->items([
                 CategoryResource::class, 
                 CityResource::class, 
                 NotificationResource::class, 
                ])
                ->sort(4),

            NavigationGroup::make('Group 2')
                ->icon('heroicon-o-collection')
                ->items([
                    // Your resources for Group 2
                    App\Filament\Resources\ReferralResource::class,
                    App\Filament\Resources\DepositResource::class,
                ])
                ->sort(2),

            // NavigationGroup::make('Group 3')
            //     ->icon('heroicon-o-archive')
            //     ->items([
            //         // Your resources for Group 3
            //         \Filament\Resources\ReportResource::class,
            //     ])
            //     ->sort(3),
        ];
    }
}
