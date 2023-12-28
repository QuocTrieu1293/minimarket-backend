<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryGroupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client;
use App\Http\Controllers\AccountController;
use App\Models\Category;
use App\Models\OrderItem;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SaleEventController;
use App\Models\SaleEvent;
use Illuminate\Auth\Events\Registered;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     // dd('hello');
//     return $request->user();
// });

// Route::post('user/register', function(Request $request) {
//     $user = User::create($request->all());
//     event(new Registered($user));
//     return $user;
// });

Route::prefix('sanpham')->name('product.')->group(function() {
    Route::get('/', [Client\ProductController::class,'index'])
        ->name('index');
    Route::get('/phobien',[Client\ProductController::class, 'getPopulars'])
        ->name('populars');
    Route::get('/banchay',[Client\ProductController::class, 'getBestSells'])
        ->name('best-sells');
    Route::get('/search',[Client\ProductController::class,'search'])
        ->name('search');
    Route::post('/themdanhgia',[Client\ProductController::class, 'addReview'])
        ->name('add-review');
    Route::prefix('/yeuthich')->group(function() {
        Route::post('/', [Client\ProductController::class, 'addWishlist'])
            ->name('add-wishlist');
        Route::delete('/', [Client\ProductController::class, 'deleteWishlist'])
            ->name('delete-wishlist');
    });
    Route::prefix('{id}')->group(function() {
        Route::get('/',[Client\ProductController::class, 'getDetail'])
            ->name('detail');
        Route::get('lienquan',[Client\ProductController::class, 'getRelevants'])
            ->name('relevants');
        Route::get('danhgia',[Client\ProductController::class, 'getReviews'])
            ->name('reviews');
    });
    
});

Route::prefix('sale')->name('sale-event.')->group(function() {
    $controller = SaleEventController::class;

    Route::get('/', [$controller, 'getSaleEvent'])
        ->name('info');
    Route::get('/sanpham', [$controller, 'getSaleItems'])
        ->name('items');
});

Route::prefix('danhmuc')->name('category_group.')->group(function() {
    $controller = CategoryGroupController::class;
    Route::get('/', [$controller,'index'])
        ->name('index');

    Route::prefix('/{id}')->group(function() use ($controller) {
        Route::get('/', [$controller, 'getProducts'])
            ->name('products');
        Route::get('/danhmucnho', [$controller, 'getCategories'])
            ->name('categories');
        Route::get('/thuonghieu', [$controller, 'getBrands'])
            ->name('brands');
        Route::get('/random', [$controller, 'randomCategoryGroups'])
            ->name('random');
    });
    
});

Route::prefix('danhmucnho')->name('category.')->group(function() {
    Route::get('/{id}/thuonghieu', function($id) {
        try {
            $brands = Category::findOrFail($id)->products->map(
                        fn($product) => $product->brand
                    )->unique();
            return $brands;
        }catch(Exception $e) {
            return response()->json(["error" => $e->getMessage()],404);
        }
    })
    ->name('brands');
});

Route::prefix('giohang')->name('cart.')->group(function() {
    $controller = CartController::class;

    Route::prefix('/{id}')->group(function() use($controller) {
        Route::get('/', [$controller, 'getItems'])
            ->name('items');
        Route::delete('/xoa', [$controller, 'deleteItems'])
            ->name('items-delete');
    });
    Route::post('/them', [$controller, 'addItem'])
        ->name('item-add');
    Route::put('capnhat/{id}', [$controller, 'updateItem']) //cartItemId
        ->name('item-update');
    
});

Route::prefix('donhang')->name('order.')->group(function() {
    $controller = OrderController::class;

    Route::post('/them',[$controller, 'add'])
        ->name('add'); 
    Route::get('/{id}', [$controller, 'get'])
        ->name('get');
});

Route::name('account.')->group(function() {
    $controller = AccountController::class;

    Route::post('dangnhap', [$controller, 'login'])
        ->name('login');
    Route::post('dangky', [$controller, 'register'])
        ->name('register');
    Route::prefix('taikhoan/{id}')->group(function() use($controller) {
        Route::get('/thongtin', [$controller, 'getInfo'])
            ->name('get-info');
        Route::put('/capnhat', [$controller, 'updateInfo'])
            ->name('update-info');
        Route::get('/donhang', [$controller, 'getOrders'])
            ->name('get-orders');
        Route::get('/yeuthich', [$controller, 'getWishList'])
            ->name('wishlist');
    });
});

Route::get('test/{id}', function($id) {
    $query = Order::findOrFail($id)->order_items()->first()
                ->product()
                ->withoutGlobalScope('visible')->withTrashed();
    return $query->first()->thumbnail;
    // DB::enableQueryLog();
    // $query->first();
    // $log = DB::getQueryLog();
    // $log = end($log);
    // dd($log);
});