<?php

namespace App\Providers;

use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\ServiceProvider;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Nette\Utils\ImageColor;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

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

        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }
        Route::pattern('id', '\d+');
        Table::configureUsing(function (Table $table): void {
            $table
                // ->filtersLayout(FiltersLayout::AboveContentCollapsible)
                ->paginationPageOptions([10, 20, 50, 100, 'all'])
                ->defaultPaginationPageOption(20)
                ->persistSortInSession()
                ->persistSearchInSession();
        });
        Stack::configureUsing(function (Stack $stack): void {
            $stack
                ->space(2);
        });
        ImageColumn::configureUsing(function (ImageColumn $column): void {
            $column
                ->defaultImageUrl(asset('images/thumbnail_placeholder.jpg'))
                ->extraImgAttributes([
                    'loading' => 'lazy',
                    'alt' => 'hình ảnh',
                    'class' => 'rounded-md'
                ]);
        });
        TextColumn::configureUsing(function (TextColumn $column): void {
            $column
                ->size(TextColumn\TextColumnSize::Small)
                ->weight(FontWeight::Medium);
        });

        ImageEntry::configureUsing(function (ImageEntry $entry): void {
            $entry
                ->defaultImageUrl(asset('images/thumbnail_placeholder.jpg'))
                ->extraImgAttributes([
                    'alt' => 'hình ảnh',
                    'class' => 'rounded-md'
                ]);
        });
    }
}
