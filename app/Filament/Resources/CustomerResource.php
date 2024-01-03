<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Filament\Customer;
use Carbon\Carbon;
use Exception;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Support\Enums\ActionSize;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Symfony\Component\Finder\Iterator\DateRangeFilterIterator;
use Filament\Forms\Components\Grid as FormGrid;
use Filament\Forms\Components\Placeholder;

require_once app_path('MyHelper/helpers.php');

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $label = 'khách hàng';
    protected static ?string $navigationLabel = 'Khách hàng';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FormGrid::make([
                    'md' => 5,
                    'sm' => 1
                ])->schema([
                    FormGrid::make([
                        'sm' => 2
                    ]) -> schema([
                        PlaceHolder::make('created_at')
                            ->label('Tạo lúc')
                            ->content(function(?Model $record, string $operation) : string {
                                if($operation !== 'edit')
                                    return '';
                                return $record->created_at->format(DATE_FORMAT);
                            }) 
                            ->extraAttributes([
                                'class' => 'italic'
                            ], true)
                    ])
                ])->columnSpan('full')->hidden(fn(string $operation) => $operation !== 'edit')
                
                ,
            ]);
    }

    public static function table(Table $table): Table
    {
        $columnDefault = 'Chưa cập nhật';
        $dateformat = 'd/m/y G:i';
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('STT')
                    ->rowIndex()
                    ->grow(false)
                ,TextColumn::make('name')
                    ->label('Tên')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->wrap()
                    ->grow()
                ,TextColumn::make('email')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Đã sao chép địa chỉ email')
                    ->copyMessageDuration(1500)
                    ->icon('heroicon-s-envelope')
                    ->grow()
                ,TextColumn::make('phone')
                    ->label('SĐT')
                    ->searchable(isIndividual: true)
                    ->copyable(fn(Model $customer) => !empty($customer->phone))
                    ->copyMessage('Đã sao chép SĐT')
                    ->copyMessageDuration(1500)
                    ->default(new HtmlString(
                        "<span class='text-gray-500 font-medium'>$columnDefault</span>"
                    ))
                    ->icon(fn(Model $customer) => ($customer->phone)?'heroicon-s-phone':null)
                    ->grow(false)
                ,TextColumn::make('address')
                    ->label('Địa chỉ')
                    ->searchable(isIndividual: true)
                    ->wrap()
                    ->copyable(fn(Model $customer) => !empty($customer->address))
                    ->copyMessage('Đã sao chép địa chỉ')
                    ->copyMessageDuration(1500)
                    ->default(new HtmlString(
                        "<span class='text-gray-500 font-medium'>$columnDefault</span>"
                    ))
                    ->icon(fn(Model $customer) => ($customer->address)?'heroicon-s-home':null)
                    ->grow()
                ,TextColumn::make('created_at')
                    ->label('Tạo ngày')
                    ->datetime($dateformat)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    // ->state(fn(Model $customer) => $customer->created_at->format($dateformat))
                    ->searchable(query: function(Builder $query, string $search) : Builder {
                        try {
                            $datetime = Carbon::parse($search);
                            // dd($datetime);
                            $query = $query->whereDate('created_at',$datetime)
                                            ->orWhere(function($query) use($datetime) {
                                                $query->whereDay('created_at',$datetime->day)
                                                    ->whereMonth('created_at',$datetime->month)
                                                    ->whereYear('created_at', $datetime->year);
                                            });
                            
                        }catch(Exception $e) {
                           
                        }
                        return $query;
                    })
                    ->grow(false)
                    
            ])
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->persistColumnSearchesInSession()
            ->persistSearchInSession()
            ->filters([
                //
                SelectFilter::make('is_enable')
                    ->options([
                        true => 'Kích hoạt',
                        false => 'Vô hiệu'
                    ])
                    ->label('Tài khoản')
                ,Filter::make('created_at')
                ->label('Ngày tạo')
                ->form([
                    // PlaceHolder::make('')
                    //     ->content('Ngày tạo')
                    Fieldset::make('Ngày tạo') -> schema([
                        DatePicker::make('from')
                        ->label('Từ ngày')
                        ,DatePicker::make('until')
                        ->label('Đến ngày')
                    ])
                    ->label(new HtmlString('
                        <span style="font-size:16px">
                            <span style="background:white;">Ngày</span> 
                            <span style="background:white;">tạo</span>
                        </span>
                    '))
                    ->columns(2)
                ])
                ->query(function(Builder $query, array $data) : Builder {
                    return $query
                        ->when(
                            $data['from'],
                            fn($query, $date) => $query->whereDate('created_at', '>=', $date)
                        )
                        ->when(
                            $data['until'],
                            fn($query, $date) => $query->whereDate('created_at', '<=', $date)
                        );
                })
                ->indicateUsing(function(array $data) : string {
                    $date_format = 'd/m/y';
                    if($data['from'] && $data['until']) {
                        return 'Ngày tạo: từ ' . Carbon::parse($data['from'])->format($date_format) . ' đến ' . Carbon::parse($data['until'])->format($date_format);
                    }else if($data['from']) {
                        return 'Ngày tạo: sau ' . Carbon::parse($data['from'])->format($date_format);
                    }else if($data['until']){
                        return 'Ngày tạo: trước ' . Carbon::parse($data['until'])->format($date_format);
                    }
                    // var_dump($data);
                    return '';
                })
                ->columnSpan(2)

            ])
            ->filtersFormColumns(2)
            ->filtersTriggerAction(function(TableAction $action) {
                $action
                ->button()
                ->label('Lọc')
                ->badgeColor(Color::Yellow)
                ;
            })
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\Action::make('enable')
                    ->label('Kích hoạt')
                    ->action(function(Customer $customer) {
                        $customer->is_enable = true;
                        $customer->save();
                    })
                    ->hidden(fn(Customer $customer) => $customer->is_enable)
                    ->button()->color('info')->size(ActionSize::Medium)
                ,Tables\Actions\Action::make('disable')
                    ->label('Vô hiệu')
                    ->action(function(Customer $customer) {
                        $customer->is_enable = false;
                        $customer->save();
                    })
                    ->hidden(fn(Customer $customer) => (!$customer->is_enable))
                    ->button()->color(Color::Slate)->size(ActionSize::Medium)
                ,Tables\Actions\DeleteAction::make()
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->poll('15s')
            ;
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            // 'create' => Pages\CreateCustomer::route('/create'),
            // 'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }    
}
