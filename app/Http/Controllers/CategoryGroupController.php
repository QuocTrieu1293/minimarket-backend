<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\CategoryGroup;
use App\Models\OrderItem;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryGroupController extends Controller
{
    public function index() {
        return CategoryGroup::all();
    }

    public function getCategories($id) {
        try {
            $group = CategoryGroup::findOrFail($id);
            $categories = $group->categories;
            return [
                'categoryGroupName' => $group->name,
                'list' => $categories 
            ];
        }catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function getProducts(Request $request, $id) {
        try {
            $perPage = 16;
            $query = CategoryGroup::findOrFail($id)
                    ->through('categories')->has('products'); //hasManyThrough
            if($categoryId = $request->query('categoryId')) {
                $query = $query->where('category.id', $categoryId);
            }
            $query = match($request->query('sort')) {
                'banchay' => $query->orderByDesc(
                                OrderItem::selectRaw('sum(quantity)')
                                ->where('product_id','product.id')
                            ),
                'tenaz' => $query->orderBy('name', 'asc'),
                'tenza' => $query->orderByDesc('name'),
                'giathap' => $query->orderByRaw('IFNULL(event_price, discount_price) asc'),
                'giacao' => $query->orderByRaw('IFNULL(event_price, discount_price) desc'),
                default => $query
            };
            // dd($query->toSql());
            if($brand = $request->query('brand')) {
                $query = $query->where('brand_id',$brand);
            }
            if($range = $request->query('range')) {
                $range = explode('-',$range);
                $min = ((int) $range[0]) * 1000; $max = ((int) $range[1]) * 1000;
                if($min) {
                    $query = $query->where(function($query) use($min) {
                        $query->where(function($query) use($min) {
                            $query->whereNotNull('event_price')->where('event_price','>=',$min);
                        })->orWhere(function($query) use($min) {
                            $query->whereNull('event_price')->where('discount_price','>=',$min);
                        });
                    });
                }
                if($max) {
                    $query = $query->where(function($query) use($max) {
                        $query->where(function($query) use($max) {
                            $query->whereNotNull('event_price')->where('event_price','<=',$max);
                        })->orWhere(function($query) use($max) {
                            $query->whereNull('event_price')->where('discount_price','<=',$max);
                        });
                    });
                }
            }
            // DB::enableQueryLog();
            // $query->get();
            // $log = DB::getQueryLog();
            // $log = end($log);
            // dd($log);
            return ProductResource::collection($query->paginate($perPage)); 
            // return $query->paginate($perPage); 
        }catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function getBrands($id) {
        try {
            
            //Has Many Through: lấy tất cả products của một có trong một category group
            $products = CategoryGroup::findOrFail($id)
            ->through('categories')->has('products')->get();

            $brands = $products->map(fn($product) => $product->brand)->unique();
            return $brands;
        }catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function randomCategoryGroups($id) {
        return CategoryGroup::where('id','<>',$id)->take(4)->get();
    }
}
