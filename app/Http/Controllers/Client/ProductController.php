<?php

namespace App\Http\Controllers\Client;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\WishlistResource;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Wishlist;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    // const attributes = [
    //     'id','thumbnail','name','reg_price','discount_percent','discount_price','canonical','quantity',
    //     'rating','category_id','brand_id'
    // ];

    // public function index() {
    //     $response = Product::select(...ProductController::attributes)->inRandomOrder()->paginate(20);
    //     return $response;
    // }

    public function index()
    {
        $products = Product::inRandomOrder()->paginate(20);
        return ProductResource::collection($products);
    }

    public function getRelevants($id)
    {
        try {
            $category_id = Product::findOrFail($id)->category_id;
            // return Product::where('category_id',$category_id)->where('id','<>',$id)
            //         ->inRandomOrder()->take(5)->get();
            $products = Product::where('category_id', $category_id)->where('id', '<>', $id)
                ->inRandomOrder()->take(5)->get();
            return [
                'data' => ProductResource::collection($products),
                'page' => 0,
            ];
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 404);
        }
    }

    public function getDetail($id)
    {
        // var_dump(
        //     Product::where('id',$id)->select(...ProductController::attributes)
        //     ->addSelect('quantity','description','article')->get()
        // );
        // $product = Product::where('id',$id)->select(...ProductController::attributes)
        // ->addSelect('quantity','description','article')
        // ->with(['galleries' => function($query) {
        //     $query->select('thumbnail','sort','product_id')->orderBy('sort','asc');
        // }])
        // ->with([
        //     'brand' => fn($query) => $query->select('id', 'name'),
        //     'category' => fn($query) => $query->select('id', 'name')
        // ])
        // ->first();
        // return response()->json($product);
        try {
            $product = Product::findOrFail($id);
            return new ProductDetailResource($product);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function getReviews($id)
    {
        try {
            $reviews = Product::findOrFail($id)->reviews()->orderByDesc('created_at')->get();
            return response()->json(ReviewResource::collection($reviews));
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 404);
        }
    }

    public function getPopulars()
    {
        $response = Redis::get('popular_products');
        if (!$response) {
            $category_cnt = 5;
            $productsPerCategory_cnt = 24;
            $minProductsPerCategory_cnt = 12;
            $categories = Category::withCount(['products' => function ($query) {
                $query->where('is_featured', 1);
            }])->having('products_count', '>', 0)->orderBy('products_count', 'desc')->take($category_cnt)->get();
            // dd($categories);
            if ($categories->count() < $category_cnt) { //Trường hợp không đủ 5 category thì lấy thêm cho đủ
                $categories = $categories->concat(Category::has('products', '>=', $minProductsPerCategory_cnt)
                    ->whereNotIn('id', $categories->pluck('id')->toArray())
                    ->inRandomOrder()
                    ->take(5 - ($categories->count()))
                    ->get());
            }
            $response = [];
            $productId = [];
            foreach ($categories as $category) {
                $record = [
                    'categoryID' => $category->id,
                    'name' => $category->name,
                    'category_group_id' => $category->category_group_id,
                ];
                $products = $category->products()->orderBy('is_featured', 'desc')->take($productsPerCategory_cnt)->get();
                $productId = array_merge($productId, $products->modelKeys());
                if ($products->count() < $minProductsPerCategory_cnt) {
                    $addProducts = Product::whereNotIn('id', $productId)
                        ->whereNotIn('category_id', $categories->pluck('id'))
                        ->inRandomOrder()
                        ->take($minProductsPerCategory_cnt - ($products->count()))
                        ->get();
                    $products = $products->concat($addProducts);
                    $productId = array_merge($productId, $addProducts->modelKeys());
                }

                // $record['products'] = $products->map(function($product){
                //     return $product->only(ProductController::attributes);
                // });
                // ---------------------------------------------------------------
                $record['products'] = ProductResource::collection($products);

                $response[] = $record;
            }
            Redis::setex('popular_products', 300, json_encode($response));
        }

        return $response;
    }

    public function getBestSells()
    {
        $response = Redis::get('best_sell');
        if (!$response) {
            $cnt = 6;
            $query = Product::withSum('order_items as total_sell', 'quantity')
                ->orderByDesc('total_sell')
                ->orderByDesc('created_at')
                ->take($cnt * 3);
            $noibat = $query->take($cnt)->get();
            $hangmoi = $query->orderByDesc('created_at')->take($cnt)->get();
            $phobien = $query->whereNotIn('id', array_merge($noibat->modelKeys(), $hangmoi->modelKeys()))
                ->take($cnt)->get();
            $response = [
                [
                    'type' => 'Nổi bật',
                    'query' => 'noibat',
                    'products' => ProductResource::collection($noibat)
                ],
                [
                    'type' => 'Phổ biến',
                    'query' => 'phobien',
                    'products' => ProductResource::collection($phobien)
                ],
                [
                    'type' => 'Hàng mới',
                    'query' => 'hangmoi',
                    'products' => ProductResource::collection($hangmoi)
                ]
            ];
            Redis::setex('best_sell', 300, json_encode($response));
        }
        return $response;
    }

    public function search(Request $request)
    {
        $keyword = $request->keyword;
        $products = Product::where('name', 'like', "%{$keyword}%")->get();
        // var_dump($product);
        // echo $product->count();

        // return $products;
        return response()->json(ProductResource::collection($products));
    }

    public function addReview(Request $request)
    {
        $rules = [
            'userId' => 'required|exists:users,id',
            'productId' => 'required|exists:product,id',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:5000',
            'rating' => 'bail|required|integer|between:1,5'
        ];
        $messages = [
            'userId.required' => 'userId không được để trống',
            'productId.required' => 'productId không được để trống',
            'title.max' => 'Tiêu đề tối đa 255 kí tự',
            'comment.max' => 'Nội dung tối đa 5000 kí tự',
            'rating.required' => 'Vui lòng đánh giá sản phẩm',
            'rating.integer' => 'Điểm đánh giá là số nguyên từ 1 đến 5',
            'rating.between' => 'Đánh giá phải là số nguyên từ 1 đến 5',
            'exists' => ':attribute không tồn tại trong csdl'
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "Failed to add review",
                "error" => $validator->messages()
            ], 422);
        }
        $review = Review::create([
            'user_id' => $request->userId,
            'product_id' => $request->productId,
            'title' => $request->title,
            'comment' => $request->comment,
            'rating' => $request->rating
        ]);
        // dd($review);
        if (is_null($review))
            return response()->json([
                "status" => "error",
                "message" => "Failed to add review",
                "error" => "Thêm đánh giá thất bại"
            ], 422);
        //tính điểm rating trung bình
        $product = $review->product;
        $product->update(['rating' => $product->reviews()->avg('rating')]);
        $reviews = $product->reviews()->orderByDesc('created_at')->get();
        return response()->json(ReviewResource::collection($reviews), 201);
    }

    public function addWishlist(Request $request)
    {
        try {
            $product = Product::findOrFail($request->productId);
            $user = User::role('customer')->findOrFail($request->userId);
            Wishlist::updateOrCreate(
                ['user_id' => $user->id, 'product_id' => $product->id]
            );
            return response()->json(WishlistResource::collection($user->wishlists));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function deleteWishlist(Request $request)
    {
        try {
            $user = User::role('customer')->findOrFail($request->userId);
            $user->wishlists()->where('product_id', $request->productId)->delete();
            return response()->json(WishlistResource::collection($user->wishlists));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
