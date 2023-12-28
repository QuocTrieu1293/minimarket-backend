<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Filament\ProductFilament;
use App\Models\Unit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Livewire\Component as Livewire;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Textarea;
use Guava\FilamentClusters\Forms\Cluster;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use App\Models\Brand;
use App\Models\Gallery;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Grid as FormGrid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Support\Enums\ActionSize;
use Livewire\Component;
use Illuminate\Support\Str;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Contracts\HasTable;

require_once app_path('MyHelper/helpers.php');

class ProductResource extends Resource
{
    protected static ?string $model = ProductFilament::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'sản phẩm';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([        
                FormGrid::make([
                    'md' => 5,
                    'sm' => 2
                ])->schema([
                    FormGrid::make([
                        'sm' => 2
                    ]) -> schema([
                        PlaceHolder::make('create')
                            ->label('Tạo lúc')
                            ->content(function(?Model $record, string $operation) : string {
                                if($operation !== 'edit')
                                    return '';
                                return $record->created_at->format(DATE_FORMAT);
                            }) 
                            ->extraAttributes([
                                'class' => 'italic'
                            ], true)
    
                        ,PlaceHolder::make('update')
                            ->label('Cập nhật')
                            ->content(function(?Model $record, string $operation) : string {
                                if($operation !== 'edit')
                                    return '';
                                if(empty($record->updated_at))
                                    return 'Chưa có cập nhật';
                                return $record->updated_at->format(DATE_FORMAT);
                            })
                            ->extraAttributes([
                                'class' => 'italic'
                            ], true) 
                    ])->columnStart([
                        'md' => 4,
                        'sm' => 1,
                    ])
                ])->columnSpan('full')->hidden(fn(string $operation) => $operation !== 'edit')
                
                ,FormGrid::make([
                    'md' => 4
                ])->schema([
                    FormGrid::make(['sm'=>2])->schema([
                        Toggle::make('is_featured')
                            ->label('Featured')
                            ->onColor(Color::hex('#f59e0b'))
                            ->offColor(Color::hex('#6b7280'))
                            ->default(false)
                            ->helperText('Là sản phẩm đặc trưng')
                    
                        ,Toggle::make('is_visible')
                            ->label('Hiển thị')
                            ->onIcon('heroicon-s-eye')->onColor(Color::Emerald)
                            ->offIcon('heroicon-s-eye-slash')->offColor('danger')
                            ->default(true)
                            ->helperText('Ẩn hoặc hiện sản phẩm trên trang web')
                    ])->columnStart(1)->columnSpan(['sm'=>2])
                ])
                ->columnSpan('full')
                    
                ,Section::make('Thông tin chung')->schema([
                    TextArea::make('name')
                        ->label('Tên sản phẩm')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->validationAttribute('name')
                        ->afterStateUpdated(function(
                            Set $set, ?string $state, TextArea $component, Livewire $livewire
                        ) {
                            // $str = spaceFomat($state);
                            // $set('name',ucfirst($str));
                            $component->state(ucfirst(spaceFomat($component->getState())));
                            $livewire->validateOnly($component->getStatePath());

                        })
                        ->live(onBlur: true)
                        ->autosize()

                    ,Select::make('category_id')
                        ->required()
                        ->preload()
                        ->label('Danh mục')
                        ->validationAttribute('category')
                        ->placeholder('Chọn danh mục sản phẩm')
                        ->native(false)
                        ->relationship(name: 'category', titleAttribute: 'name',
                            modifyQueryUsing: fn(Builder $query) => $query->orderBy('name', 'asc')
                        )
                        ->searchable()
                        ->searchPrompt('Tìm tên danh mục')
                        ->noSearchResultsMessage('Không có danh mục phù hợp với tìm kiếm')
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('Tên danh mục')
                                ->validationAttribute('category name')
                                ->maxLength(200)
                                ->required()
                                ->unique(Category::class, 'name')
                                ->markAsRequired(false)
                                ->afterStateUpdated(function(TextInput $component, Livewire $livewire) {
                                    $component->state(ucfirst(spaceFomat($component->getState())));
                                    $livewire->validateOnly($component->getStatePath());
                                })
                                ->live(onBlur: true)
                            ,Select::make('category_group_id')
                                ->required()
                                ->label('Nhóm danh mục')
                                ->relationship('category_group','name',
                                    fn($query) => $query->orderBy('name', 'asc')
                                )->native(false)->placeholder('Chọn nhóm danh mục')
                                ->markAsRequired(false)
                                ->validationAttribute('category group')
                        ])
                    ->selectablePlaceholder(false)
                    ,Select::make('brand_id')
                        ->required()
                        ->preload()
                        ->label('Thương hiệu')
                        ->validationAttribute('brand')
                        ->placeholder('Chọn thương hiệu')
                        ->native(false)
                        ->relationship(name: 'brand', titleAttribute: 'name',
                            modifyQueryUsing: fn(Builder $query) => $query->orderBy('name', 'asc')
                        )
                        ->searchable()
                        ->searchPrompt('Tìm tên thương hiệu')
                        ->noSearchResultsMessage('Không có thương hiệu phù hợp với tìm kiếm')
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('Tên thương hiệu')
                                ->validationAttribute('brand name')
                                ->maxLength(200)
                                ->required()
                                ->unique(Brand::class, 'name')
                                ->markAsRequired(false)
                                ->afterStateUpdated(function(TextInput $component, Livewire $livewire) {
                                    $component->state(ucwords(spaceFomat($component->getState())));
                                    $livewire->validateOnly($component->getStatePath());
                                })
                                ->live(onBlur: true)
                        ])
                        ->selectablePlaceholder(false)
                ])->columns(1)->columnSpan(5)
                ->collapsible()->icon('heroicon-s-shopping-cart')
                
                ,Section::make('Giá sản phẩm')->schema([
                    TextInput::make('reg_price')
                        ->label('Giá bán')
                        ->suffix("₫") ->prefixIcon('heroicon-m-banknotes')
                        ->minValue(0)
                        ->rules(['max_digits:10'])
                        ->required()
                        ->validationAttribute('price')
                        ->afterStateUpdated(function(TextInput $component, Livewire $livewire, Get $get, Set $set, ?string $state) {
                            $livewire->validateOnly($component->getStatePath());  
                            $set('discount_price',
                                moneyFormat(round(
                                    (int)$state *
                                    (100-(int)$get('discount_percent')) / 100
                                ))
                            );
                        })
                        ->live(onBlur: true)
                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                        
                    ,Cluster::make([
                        TextInput::make('discount_percent')
                            ->label('% Khuyến mãi')
                            ->placeholder('% Khuyến mãi')
                            ->suffix("%") ->prefixIcon('heroicon-m-receipt-percent')
                            ->rules(['integer'])
                            ->minValue(0)
                            ->maxValue(100)
                            ->nullable()
                            ->validationAttribute('percentage discount')
                            ->afterStateUpdated(function(TextInput $component, Livewire $livewire, Get $get, Set $set, ?string $state) {
                                $livewire->validateOnly($component->getStatePath());  
                                $set('discount_price',
                                    moneyFormat(round(
                                        (int)$get('reg_price') *
                                        (100-(int)$state) / 100
                                    ))
                                );
                            })
                            ->live(onBlur: true)

                        ,TextInput::make('discount_price')
                            ->label('Giá khuyến mãi')
                            ->placeholder('Giá khuyến mãi')
                            ->readOnly()
                            ->nullable()
                            ->prefixIcon('heroicon-m-shopping-bag')->suffix("₫")
                            ->extraInputAttributes([
                                'class' => 'font-semibold text-2xl'
                            ], true)
                    ])->columns(1)->label('Khuyến mãi')
                ])->columns(1)->columnSpan(4)
                ->collapsible()->icon('heroicon-s-credit-card')

                ,Section::make('Số lượng & Đơn vị đo')->schema([
                    Cluster::make([
                        TextInput::make('quantity')
                            ->label('Số lượng')
                            ->validationAttribute('quantity')
                            ->minValue(0)
                            ->rules(['integer'])
                            ->numeric()
                            ->inputMode('numeric')
                            ->nullable()
                            ->afterStateUpdated(function(TextInput $component, Livewire $livewire) {
                                $livewire->validateOnly($component->getStatePath());  
                            })
                            ->live(onBlur: true)
                            ->columnSpan(3)
                    
                        ,Select::make('unit')
                            ->preload()
                            ->native(false)
                            ->options(function() : array {
                                $units = DB::table('units')->get()->toArray();
                                foreach($units as $unit) {
                                    $arr[] = $unit->name;
                                }
                                // dd($arr);
                                return array_combine($arr, $arr);
                            })
                            ->placeholder('ĐVT')
                            ->columnSpan(1)
                            ->searchable()
                            ->searchPrompt('Tìm ĐVT')
                            ->noSearchResultsMessage('🥲')
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(Unit::class,'name')
                            ])
                            ->createOptionUsing(function($data) {
                                // dd($data);
                                //$data chỉ chứa một giá trị duy nhất của field 'name'
                                $new = new Unit;
                                $new->name = $data['name'];
                                // dd($new);
                                $new->save();
                            })
                            ->selectablePlaceholder(false)
                            ->requiredWith('quantity')
                            ->columnSpan(2)
    
                    ])->label('Số lượng')->columns(5)
    
                    ,Cluster::make([
                        TextInput::make('canonical')
                            ->regex('/^\d+(\.\d+)?(-\d+(\.\d+)?)?$/')
                            ->validationAttribute('measurement unit')
                            ->nullable()
                            ->placeholder('vd: 400-500 hoặc 0.9-1.1')
                            ->afterStateUpdated(function(TextInput $component, Livewire $livewire) {
                                $livewire->validateOnly($component->getStatePath());  
                            })
                            ->live(onBlur: true)
                            ->columnSpan(3)
                
                        ,Select::make('_unit')
                            ->native(false)
                            ->options([
                                'Trọng lượng' => [
                                    'kg' => 'kg',
                                    'g' => 'g',
                                    
                                ]
                                ,'Dung tích' => [
                                    'lít' => 'lít',
                                    'ml' => 'ml',
                                ]
                            ])
                            ->default('g')
                            ->requiredWith('canonical')
                            ->selectablePlaceholder(false)
                            ->placeholder('ĐVĐ')
                            ->columnSpan(2)
                    ])->label('Đơn vị đo')->columns(5)
                ])->columns(1)->columnSpan(4)
                ->collapsible()->icon('heroicon-s-scale')

                ,Section::make('Hình ảnh, mô tả và bài viết về sản phẩm') -> schema([
                    Tabs::make('gal_desc_art')
                    ->tabs([
                        Tabs\Tab::make('galleryTab')
                            ->label('Hình ảnh')
                            ->schema([
                                PlaceHolder::make('count')
                                    ->label('')
                                    ->content(fn(Get $get) : string => 
                                        'Số ảnh: ' . count($get('galleries'))
                                    )
                                    ->extraAttributes([
                                        'style' => 'font-size:16px;font-weight:500'
                                    ], true)

                                ,Repeater::make('galleries') 
                                   -> schema([
                                        ViewField::make('thumbnail')
                                            ->view('product.thumbnail')
                                            ->viewData(['width' => 200])
                                    ])
                                    ->label('')
                                    ->defaultItems(0)
                                    ->addAction(function(Action $action) {
                                        $action
                                            ->label('Thêm ảnh')
                                            ->icon('heroicon-s-plus')
                                            ->iconPosition(IconPosition::After)
                                            ->color(Color::hex('#0284c7'))
                                            ->size(ActionSize::Medium)
                                            ->form([
                                                TextInput::make('url')
                                                    ->label('')
                                                    ->validationAttribute('image URL')
                                                    ->suffixIcon('heroicon-o-globe-alt')
                                                    ->placeholder('Đường dẫn tới ảnh sản phẩm')
                                                    ->activeUrl()
                                                    ->afterStateUpdated(function (?string $state, Livewire $livewire) {
                                                        $livewire->validateOnly($state);
                                                    })->live(onBlur: true)
                                                    ->required(fn(Get $get) : bool => (count($get('imageFile')) == 0))
                                                    //require nếu FileUpload không có file nào
                                                
                                                ,FileUpload::make('imageFile')
                                                    ->label('File ảnh')
                                                    ->validationAttribute('uploaded file')
                                                    ->image()
                                        
                                            ])
                                            ->modalSubmitActionLabel('Thêm')
                                            ->action(function(array $data, Repeater $component) {
                                                if(!empty($data['imageFile'])) {
                                                    // dd($data['imageFile']); //tên file upload
                                                    $thumbnail = asset('storage/' . $data['imageFile']);
                                                }else {
                                                    $thumbnail = $data['url'];
                                                }

                                                $newUuid = (string) Str::uuid();
                                                $items = $component->getState();
                                                $items[$newUuid] = [];
                                                $component->state($items);
                                                $component->getChildComponentContainers()[$newUuid]->fill([
                                                    'thumbnail' => $thumbnail
                                                ]);
                                                $component->collapsed(false, shouldMakeComponentCollapsible: false);
                                            })
                                        ;
                                    })
                                    ->reorderAction(function(Action $action) {
                                        $action
                                            ->icon('heroicon-o-arrow-path-rounded-square')
                                            ->color(Color::hex('#ea580c'))
                                        ;
                                    })
                                    ->collapsible()
                                    ->relationship()
                                    ->orderColumn('sort')
                                    ->grid(3)
                                    ->live()
                                    
                            ])
                            ->icon('heroicon-m-photo')
                            ->iconPosition(IconPosition::After)
                        
                        ,Tabs\Tab::make('descTab')
                            ->label('Mô tả')
                            ->schema([
                                Textarea::make('description')
                                    ->label('')
                                    ->validationAttribute('product desciption')
                                    ->nullable()
                                    ->rows(7)
                                    ->maxLength(1000)
                            ])
                            ->icon('heroicon-m-document-text')
                            ->iconPosition(IconPosition::After)
                            
                        ,Tabs\Tab::make('articleTab')
                            ->label('Bài viết')
                            ->schema([
                                RichEditor::make('article')
                                    ->label('')
                                    ->enableToolbarButtons([])
                                    ->disableToolbarButtons(['codeBlock'])
                                    ->fileAttachmentsDirectory('product/article/attachments')
                                    ->nullable()
                            ])
                            ->icon('heroicon-m-pencil')
                            ->iconPosition(IconPosition::After),
                    ])->columnSpan('full')
                    ->contained(false)
                ])
                ->icon('heroicon-s-square-3-stack-3d')
                ->collapsible()->compact()->columnSpan('full')
                
            ])->columns(13)
            ;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Grid::make(['md'=>3]) -> schema([

                    ImageColumn::make('thumbnail')
                        ->label('Ảnh SP')
                        ->width(130)
                        ->height(130)
                        ->extraImgAttributes([
                            'alt' => 'ảnh sản phẩm',
                            'width' => 'auto'
                        ], true)
                        ->alignCenter()
                        

                    ,Grid::make(['sm' => 2]) -> schema([
                        TextColumn::make('name')
                            ->wrap() -> columnSpan('full')
                            ->label('Tên sản phẩm') -> sortable()
                            ->searchable()
                            ->weight(FontWeight::SemiBold)
                            ->size(TextColumn\TextColumnSize::Small)
                        ,Stack::make([
                            TextColumn::make('brand.name')
                                ->prefix('Thương hiệu: ')
                            ,TextColumn::make('category.name')
                                ->prefix('Danh mục: ')
                                ->wrap()
                            ,TextColumn::make('quantity')
                                ->prefix('SL: ')
                                ->label('Số lượng')->sortable()
                        ])
                        ,Stack::make([
                            TextColumn::make('reg_price')
                                ->prefix('Giá bán: ')
                                ->numeric(0,',','.')
                                ->money('VND')
                                ->alignRight()
                                ->label('Giá bán')->sortable()
                            ,TextColumn::make('discount_price')
                                ->prefix('Giá KM: ')
                                ->numeric(0,',','.')
                                ->money('VND')
                                ->alignRight()
                                ->label('Giá khuyến mãi')->sortable()
                            ,TextColumn::make('discount_percent')
                                ->icon('heroicon-m-receipt-percent')
                                ->iconPosition(IconPosition::Before)
                                ->color(Color::hex('#d97706'))
                                ->numeric(0,',','.')
                                ->suffix('%')
                                ->alignRight()
                                ->label('% Khuyến mãi')->sortable()
                        ]) ->grow(false)

                        ,Grid::make(['sm' => 2])->schema([
                           
                            IconColumn::make('is_visible')
                                ->boolean()
                                ->trueIcon('heroicon-m-eye')
                                ->trueColor(Color::Emerald)
                                ->falseIcon('heroicon-m-eye-slash')
                                ->falseColor('danger')
                                ->tooltip(
                                    fn(ProductFilament $record) : string => $record->is_visible?"Hiển thị":"Không được hiển thị"
                                )
                                ->grow(false)

                            ,ToggleColumn::make('is_featured')
                                ->offColor(Color::hex('#6b7280'))
                                ->onColor(Color::hex('#f59e0b'))
                                ->alignRight()
                                ->grow(false)
                                ->tooltip(
                                    fn(ProductFilament $record) => $record->is_featured?'featured':'not featured'
                                )

                        ])->columnSpan('full')

                    ])->columnSpan(2)

                    ,Panel::make([
                        Split::make([
                            TextColumn::make('created_at')
                                ->prefix('Tạo lúc: ')
                                ->dateTime('G:i d/m/Y')
                                ->label('Thời điểm tạo')->sortable()
                                
                            ,TextColumn::make('updated_at')
                                ->prefix('Cập nhật: ')
                                ->since()
                                ->label('Thời điểm cập nhật')->sortable() 
                        ])
                    ])->columnSpan('full')
                    
                ])
            ]) -> contentGrid([
                'xl' => 2
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                TernaryFilter::make('is_featured')
                    ->label('Sản phẩm đặc trưng')
                    ->placeholder('Tất cả')
                ,TernaryFilter::make('is_visible')
                    ->label('Được hiển thị')
                    ->placeholder('Tất cả')
                ,SelectFilter::make('brand')
                    ->label('Thương hiệu')
                    ->relationship('brand', 'name')
                    ->searchable()->preload()
                    
                ,SelectFilter::make('category')
                    ->label('Danh mục')
                    ->relationship('category', 'name')
                    ->searchable()->preload()
                
                ,Filter::make('created_at')
                    ->label('Ngày tạo')
                    ->form([
                        // PlaceHolder::make('')
                        //     ->content('Ngày tạo')
                        FieldSet::make('Ngày tạo') -> schema([
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
            ->filtersTriggerAction(function(TableAction $action) {
                $action
                ->button()
                ->label('Lọc')
                ->badgeColor(Color::Yellow)
                ;
            })
            ->filtersFormColumns(4)
            ->persistFiltersInSession()
            ->actions([
                
                Tables\Actions\EditAction::make()
                ,Tables\Actions\DeleteAction::make()

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->recordUrl(null)
            ->poll('15s')
            ->emptyStateDescription('')
            ->headerActions([
                \Filament\Tables\Actions\CreateAction::make()
            ])
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }    

}
