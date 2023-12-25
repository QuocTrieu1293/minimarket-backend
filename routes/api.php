<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryGroupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client;
use App\Models\Category;
use App\Models\OrderItem;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('sanpham')->name('product.')->group(function() {
    Route::get('/', [Client\ProductController::class,'index'])
        ->name('index');
    Route::get('/phobien',[Client\ProductController::class, 'getPopulars'])
        ->name('populars');
    Route::get('/banchay',[Client\ProductController::class, 'getBestSells'])
        ->name('best-sells');
    Route::get('search',[Client\ProductController::class,'search'])
        ->name('search');
    Route::prefix('{id}')->group(function() {
        Route::get('/',[Client\ProductController::class, 'getDetail'])
            ->name('detail');
        Route::get('lienquan',[Client\ProductController::class, 'getRelevants'])
            ->name('relevants');
        Route::get('reviews',[Client\ProductController::class, 'getReviews'])
            ->name('reviews');
        Route::post('themdanhgia',[Client\ProductController::class, 'addReview'])
            ->name('add-review');
    });
    
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

Route::post('test', function(Request $request) {
    $item = OrderItem::create([
        'unit_price' => $request->unit_price,
        'quantity' => $request->quantity,
        'user_id' => $request->user_id,
        'product_id' => $request->product_id
    ]);
    return $item->total_price;
});