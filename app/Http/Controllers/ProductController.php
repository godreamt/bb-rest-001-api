<?php

namespace App\Http\Controllers;

use App\Product;
use App\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class ProductController extends Controller
{
    public function getCategories(Request $request) {
        $fields = $request->get('fields', '*');
        if($fields != '*'){
            $fields = explode(',',$fields);
        }
        $categories = Category::select($fields)->with('branch');

        if(!empty($request->searchString)) {
            $categories = $categories->where('categoryName', 'LIKE', '%'.$request->searchString.'%');
        }

        if(!empty($request->status)) {
            $categories = $categories->where('isActive', ($request->status == 'in-active')?false:true);
        }

        if(!empty($request->orderCol) && !empty($request->orderType)) {
            $categories = $categories->orderBy($request->orderCol, $request->orderType);
        }
        $currentPage = $request->pageNumber;
        if(!empty($currentPage)){
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            return $categories->paginate(10);
        }else {
            return $categories->get();
        }
    }

    public function getCategoryDetail(Request $request, $id) {
        return Category::find($id);
    }

    public function createCategory(Request $request) {
        return \DB::transaction(function() use($request) {
            try {
                $category = new Category();
                $category->categoryName = $request->categoryName;
                $category->description = $request->description;
                $category->branch_id = $request->branch_id;
                $category->isActive = true;
                $category->save();
                return ['data' => $category, 'msg'=> "Category created successfully"];
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not create category data'], 404);
            }
        });
    }

    public function updateCategory(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $category = Category::find($id);
                if($category instanceof Category) {
                    $category->categoryName = $request->categoryName;
                    $category->description = $request->description;
                    $category->branch_id = $request->branch_id;
                    $category->isActive = $request->isActive;
                    $category->save();
                    return ['data' => $category, 'msg'=> "Category updated successfully"];
                }else {
                    return response()->json(['msg' => 'Category Does not exist'], 404);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not update category data'], 404);
            }
        });
    }

    public function deleteCategory(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $category = Category::find($id);
                if($category instanceof Category) {
                    $category->delete();
                    return ['data' => $category, 'msg'=> "Category deleted successfully"];
                }else {
                    return response()->json(['msg' => 'Category Does not exist'], 404);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not delete category'], 404);
            }
        });
    }

    public function changeCategoryStatus(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $category = Category::find($id);
                if($category instanceof Category) {
                    $category->isActive = $request->isActive;
                    $category->save();
                    return ['data' => $category, 'msg'=> "Category status updated successfully"];
                }else {
                    return response()->json(['msg' => 'Category Does not exist'], 404);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Category status can not changed'], 404);
            }
        });
    }

    public function getProducts(Request $request) {
        $fields = $request->get('fields', '*');
        if($fields != '*'){
            $fields = explode(',',$fields);
        }
        $products = Product::select($fields)->with('branch');

        if(!empty($request->searchString)) {
            $products = $products->where(function($q) use ($request) {
                $q->where('productNumber', 'LIKE', '%'.$request->searchString.'%')
                  ->orWhere('productName', 'LIKE', '%'.$request->searchString.'%');
            });
        }
        if(!empty($request->productNumber)) {
            $products = $products->where('productNumber', 'LIKE', '%'.$request->productNumber.'%');
        }

        if(!empty($request->status)) {
            $products = $products->where('isActive', ($request->status == 'in-active')?false:true);
        }
        if(!empty($request->orderCol) && !empty($request->orderType)) {
            $products = $products->orderBy($request->orderCol, $request->orderType);
        }
        $currentPage = $request->pageNumber;
        if(!empty($currentPage)){
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            return $products->paginate(10);
        }else {
            return $products->get();
        }
    }

    public function getProductDetail(Request $request, $id) {
        return Product::with('branch')->with('categories')->with('pricings')->find($id);
    }

    public function createProduct(Request $request) {
        return \DB::transaction(function() use($request) {
            try {
                $product = new Product();
                $product->productNumber = $request->productNumber;
                $product->productName = $request->productName;
                $product->description = $request->description;
                $product->price = $request->price;
                $product->taxPercent = $request->taxPercent;
                $product->packagingCharges = $request->packagingCharges;
                $product->isActive = $request->isActive ?? true;
                $product->isOrderTypePricing = $request->isOrderTypePricing ?? true;
                $product->isVeg = $request->isVeg ?? true;
                $product->branch_id = $request->branch_id;
                $product->save();
                if($product->isOrderTypePricing) {
                    foreach($request->orderBasedPrice as $type) {
                        unset($type['orderTypeName']);
                        $product->pricings()->create($type);
                    }
                }
                $categories = ($request->categories == "")?[]:$request->categories;
                if(sizeof($categories) > 0)
                    $product->categories()->sync($categories);
                return ['data' => $product, 'msg'=> "Product created successfully"];
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not create product data', 'error' => $e], 404);
            }
        });
    }

    public function updateProduct(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $product = Product::find($id);
                $product->productNumber = $request->productNumber;
                $product->productName = $request->productName;
                $product->description = $request->description;
                $product->price = $request->price;
                $product->taxPercent = $request->taxPercent;
                $product->packagingCharges = $request->packagingCharges;
                $product->isActive = $request->isActive ?? true;
                $product->isOrderTypePricing = $request->isOrderTypePricing ?? true;
                $product->isVeg = $request->isVeg ?? true;
                $product->branch_id = $request->branch_id;
                if($product->isOrderTypePricing) {
                    foreach($request->orderBasedPrice as $type) {
                        if(empty($table['id'])){
                            unset($type['orderTypeName']);
                            $product->pricings()->create($type);
                        }else {
                            $p1 = ProductOrderTypePricing::find($table['id']);
                            $p1->price = $type['price'];
                            $p1->taxPercent = $type['taxPercent'];
                            $p1->packagingCharges = $type['packagingCharges'];
                            $p1->save();
                        }
                    }
                }
                $categories = ($request->categories == "")?[]:$request->categories;
                if(sizeof($categories) > 0)
                    $product->categories()->sync($categories);
                $product->save();
                return ['data' => $product, 'msg'=> "Product updated successfully"];
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not update product data', 'error' => $e], 404);
            }
        });
    }

    public function deleteProduct(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $product = Product::find($id);
                if($product instanceof Product) {
                    $product->delete();
                    return ['data' => $product, 'msg'=> "Product deleted successfully"];
                }else {
                    return response()->json(['msg' => 'Product Does not exist'], 400);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not delete product', 'error'=> $e], 400);
            }
        });
    }

    public function changeProductStatus(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $product = Product::find($id);
                if($product instanceof Product) {
                    $product->isActive = $request->isActive;
                    $product->save();
                    return ['data' => $product, 'msg'=> "Product status updated successfully"];
                }else {
                    return response()->json(['msg' => 'Product Does not exist'], 404);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Product status can not changed'], 404);
            }
        });
    }
}
