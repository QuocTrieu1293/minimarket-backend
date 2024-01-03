<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Infolists\Components\Fieldset as InfolistFieldset;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Support\Enums\FontWeight;

require_once app_path('MyHelper/helpers.php');

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $label = 'đơn hàng';
    protected static ?string $navigationLabel = 'Đơn hàng';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('status', 'pending')->count();
        return ($pending > 0) ? $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function infolist(Infolist $infolist) : Infolist 
    {
        $columnDefault = 'Chưa cập nhật';
        return $infolist
            ->schema([
                InfolistGrid::make(7)
                    ->schema([
                        InfolistGrid::make(3)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->dateTime('G:i d/m/Y')
                                    ->label('')
                                    ->prefix('Tạo lúc: ')
                                    ->extraAttributes([
                                        'class' => 'italic'
                                    ],true)
                                ,TextEntry::make('updated_at')
                                    ->dateTime('G:i d/m/Y')
                                    ->label('')
                                    ->prefix('Cập nhật: ')
                                    ->extraAttributes([
                                        'class' => 'italic'
                                    ],true)
                            ])
                            ->columnSpan(6)
                        ,TextEntry::make('status')
                            ->label('')
                            ->formatStateUsing(fn(string $state) => match($state) {
                                'pending' => 'Chờ xử lý',
                                'processing' => 'Đang xử lý',
                                'shipped' => 'Đang giao',
                                'delivered' => 'Đã giao',
                                'cancelled' => 'Đã huỷ'
                            })
                            ->badge()
                            ->color(fn($state) => match($state) {
                                'pending' => Color::Amber,
                                'processing' => Color::Sky,
                                'shipped' => Color::Violet,
                                'delivered' => Color::Emerald,
                                'cancelled' => Color::Red,
                            })
                            ->columnStart(7)
                    ])->columnSpanFull()
                
                ,InfolistFieldSet::make('Thông tin khách hàng')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('')
                            ->icon('heroicon-s-user')
                        ,TextEntry::make('user.email')
                            ->label('')
                            ->copyable()
                            ->copyMessage('Đã sao chép địa chỉ email')
                            ->copyMessageDuration(1500)
                            ->icon('heroicon-s-envelope')
                        ,TextEntry::make('user.phone')
                            ->label('')
                            ->copyable(fn(Model $order) => !empty($order->user->phone))
                            ->copyMessage('Đã sao chép SĐT')
                            ->copyMessageDuration(1500)
                            ->icon('heroicon-s-phone')
                            ->placeholder($columnDefault)
                    ])
                    ->label(new HtmlString('
                            <span>
                                <span style="background:white;">Thông</span> 
                                <span style="background:white;">tin</span>
                                <span style="background:white;">khách</span>
                                <span style="background:white;">hàng</span>
                            </span>
                        '))
                    ->columns(3)
                    ->columnSpanFull()

                ,TextEntry::make('address')
                    ->label('Địa chỉ giao hàng')
                    ->copyable()
                    ->copyMessage('Đã sao chép địa chỉ')
                    ->copyMessageDuration(1500)
                    ->icon('heroicon-s-truck')
                    ->columnSpanFull()
                
                ,TextEntry::make('note')
                    ->label('Ghi chú đơn hàng')
                    ->default(new HtmlString(
                        "<span class='text-gray-500 font-normal italic'>Không có ghi chú</span>"
                    ))
                    ->columnSpanFull()

                ,Section::make('Chi tiết đơn hàng')
                    ->schema([
                        TextEntry::make('sanpham')
                            ->label('')
                            ->state('Sản phẩm')
                            ->columnStart(2)
                            ->alignCenter()
                            ->size(TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold)
                        ,TextEntry::make('dongia')
                            ->label('')
                            ->state('Đơn giá')
                            ->alignCenter()
                            ->size(TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold)
                        ,TextEntry::make('soluong')
                            ->label('')
                            ->state('Số lượng')
                            ->alignCenter()
                            ->size(TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold)
                        ,TextEntry::make('thanhtien')
                            ->label('')
                            ->state('Thành tiền')
                            ->alignCenter()
                            ->size(TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold)
                        
                        ,RepeatableEntry::make('order_items')
                            ->schema([
                                ImageEntry::make('product.thumbnail')
                                    ->label('')
                                    ->size(100)
                                    ->grow(false)
                                    ->alignLeft()
                                ,TextEntry::make('product.name')
                                    ->label('')
                                    ->formatStateUsing(function(string $state, Model $record) {
                                        if($record->from_event) {
                                            $state = '[SỰ KIỆN KM] ' . $state;
                                        }
                                        return $state;
                                    })
                                    ->size(TextEntrySize::Medium)
                                    ->grow()
                                ,TextEntry::make('unit_price')
                                    ->label('')
                                    ->numeric(0,',','.')
                                    ->money('VND')
                                    ->grow(false)
                                    ->size(TextEntrySize::Medium)
                                    ->alignCenter()
                                ,TextEntry::make('quantity')
                                    ->label('')
                                    ->formatStateUsing(function(string $state, Model $record) {
                                        if($record->product->unit)
                                            $state = $state . ' ' . $record->product->unit;
                                        return $state;
                                    })
                                    ->grow(false)
                                    ->size(TextEntrySize::Medium)
                                    ->alignCenter()
                                ,TextEntry::make('total_price')
                                    ->label('')
                                    ->numeric(0,',','.')
                                    ->money('VND')
                                    ->grow(false)
                                    ->size(TextEntrySize::Medium)
                                    ->alignCenter()
                            ])
                            ->label('')
                            ->columns(5)
                            ->columnSpanFull()
                            ->contained(false)
                        ,InfolistGrid::make(5)
                            ->schema([
                                TextEntry::make('total')
                                    ->label('')
                                    ->prefix('Tổng đơn hàng: ')
                                    ->numeric(0,',','.')
                                    ->money('VND')
                                    ->icon('heroicon-s-currency-dollar')
                                    ->columnStart(4)
                                    ->columnSpan(2)
                                    ->alignRight()
                                    ->size(TextEntrySize::Large)
                                    ->weight(FontWeight::SemiBold)
                            ])
                            ->columnSpanFull()
                    ])
                    ->icon('heroicon-s-information-circle')
                    ->columns(5)
                    ->collapsible()
                    ->collapsed(true)
                    ->columnSpanFull()
            ])
            ;
    }

    public static function table(Table $table): Table
    {
        $columnDefault = 'Chưa cập nhật';
        return $table
            ->columns([
                Split::make([
                    TextColumn::make('index')
                        ->label('STT')
                        ->rowIndex()
                        ->grow(false)

                    ,Stack::make([
                        TextColumn::make('user.name')
                            ->label('Tên khách hàng')
                            ->searchable()
                            ->sortable()
                            ->wrap()
                            ->grow(false)
                        ,TextColumn::make('user.email')
                            ->label('Email')
                            ->searchable()
                            ->sortable()
                            ->copyable()
                            ->copyMessage('Đã sao chép địa chỉ email')
                            ->copyMessageDuration(1500)
                            ->icon('heroicon-s-envelope')
                            ->grow(false)
                        ,TextColumn::make('user.phone')
                            ->label('SĐT')
                            ->searchable()
                            ->copyable(fn(Model $order) => !empty($order->user->phone))
                            ->copyMessage('Đã sao chép SĐT')
                            ->copyMessageDuration(1500)
                            ->icon(fn(Model $order) => ($order->user->phone)?'heroicon-s-phone':null)
                            ->grow(false)
                            
                    ])
                    ->space(1)
                    ->grow()
                    

                    ,TextColumn::make('address')
                        ->label('Địa chỉ')
                        ->searchable()
                        ->wrap()
                        ->copyable()
                        ->copyMessage('Đã sao chép địa chỉ')
                        ->copyMessageDuration(1500)
                        ->grow()
                    
                    ,TextColumn::make('total')
                        ->label('Tổng đơn hàng')
                        ->prefix('Tổng đơn hàng: ')
                        ->numeric(0,',','.')
                        ->money('VND')
                        ->sortable()
                        ->icon('heroicon-s-currency-dollar')
                        ->grow()

                    ,Stack::make([
                        TextColumn::make('created_at')
                            ->dateTime('G:i d/m/Y')
                            ->sortable()
                            ->label('Thời điểm tạo')
                            ->prefix('Tạo lúc: ')
                            ->icon('heroicon-s-clock')
                            ->grow()
                            

                        ,TextColumn::make('updated_at')
                            ->since()
                            ->sortable()
                            ->label('Thời điểm cập nhật')
                            ->prefix('Cập nhật: ')
                            ->icon('heroicon-s-arrow-path')
                            ->grow()

                    ])
                    

                    ,TextColumn::make('status')
                        ->label('Trạng thái')
                        ->formatStateUsing(fn(string $state) => match($state) {
                            'pending' => 'Chờ xử lý',
                            'processing' => 'Đang xử lý',
                            'shipped' => 'Đang giao',
                            'delivered' => 'Đã giao',
                            'cancelled' => 'Đã huỷ'
                        })
                        ->badge()
                        ->color(fn($state) => match($state) {
                            'pending' => Color::Amber,
                            'processing' => Color::Sky,
                            'shipped' => Color::Violet,
                            'delivered' => Color::Emerald,
                            'cancelled' => Color::Red,
                        })
                        ->alignRight()
                        ->grow(false)
                       
                    
                ])
                ->from('md')
                
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Chờ xử lý',
                        'processing' => 'Đang xử lý',
                        'shipped' => 'Đang giao',
                        'delivered' => 'Đã giao',
                        'cancelled' => 'Đã huỷ'
                    ])
                    ->label('Trạng thái')
                    ->columnSpan(2)
                    ->multiple()

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
                    ->columnStart(1)
                
                ,Filter::make('updated_at')
                    ->label('Cập nhật')
                    ->form([
                        // PlaceHolder::make('')
                        //     ->content('Ngày tạo')
                        FieldSet::make('Cập nhật') -> schema([
                            DatePicker::make('updated_from')
                            ->label('Từ ngày')
                            ,DatePicker::make('updated_until')
                            ->label('Đến ngày')
                        ])
                        ->label(new HtmlString('
                            <span style="font-size:16px">
                                <span style="background:white;">Cập</span> 
                                <span style="background:white;">nhật</span>
                            </span>
                        '))
                        ->columns(2)
                    ])
                    ->query(function(Builder $query, array $data) : Builder {
                        return $query
                            ->when(
                                $data['updated_from'],
                                fn($query, $date) => $query->whereDate('updated_at', '>=', $date)
                            )
                            ->when(
                                $data['updated_until'],
                                fn($query, $date) => $query->whereDate('updated_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(function(array $data) : string {
                        $date_format = 'd/m/y';
                        if($data['updated_from'] && $data['updated_until']) {
                            return 'Cập nhật: từ ' . Carbon::parse($data['updated_from'])->format($date_format) . ' đến ' . Carbon::parse($data['updated_until'])->format($date_format);
                        }else if($data['updated_from']) {
                            return 'Cập nhật: sau ' . Carbon::parse($data['updated_from'])->format($date_format);
                        }else if($data['updated_until']){
                            return 'Cập nhật: trước ' . Carbon::parse($data['updated_until'])->format($date_format);
                        }
                        // var_dump($data);
                        return '';
                    })
                    ->columnSpan(2)
            ])
            ->filtersTriggerAction(function(Action $action) {
                $action
                ->button()
                ->label('Lọc')
                ->badgeColor(Color::Yellow)
                ;
            })
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('pending')
                        ->label('Chờ xử lý')
                        ->action(function(Model $record, Table $table) {
                            $record->status = 'pending';
                            $record->save();
                        })
                    ,Tables\Actions\Action::make('processing')
                        ->label('Đang xử lý')
                        ->action(function(Model $record) {
                            $record->status = 'processing';
                            $record->save();
                        }) 
                    ,Tables\Actions\Action::make('shipped')
                        ->label('Đang giao')
                        ->action(function(Model $record) {
                            $record->status = 'shipped';
                            $record->save();
                        })
                    ,Tables\Actions\Action::make('delivered')
                        ->label('Đã giao')
                        ->action(function(Model $record) {
                            $record->status = 'delivered';
                            $record->save();
                        })
                    ,Tables\Actions\Action::make('cancelled')
                        ->label('Đã huỷ')
                        ->action(function(Model $record) {
                            $record->status = 'cancelled';
                            $record->save();
                        })
                ])
                ->label('Trạng thái mới')
                ->button()
                ->icon('')
                ->size(ActionSize::Small)
                ->color(Color::Teal)
                    
                ,Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color(Color::Sky)
                    ,Tables\Actions\DeleteAction::make()
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordAction(null)
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
            'index' => Pages\ListOrders::route('/'),
            // 'create' => Pages\CreateOrder::route('/create'),
            // 'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }    
}
