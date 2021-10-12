<?php

namespace App\Http\Controllers;

use App\Product;
use App\Category;
use App\ProductCombo;
use App\ProductComboItem;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\ProductAdvancedPricing;
use Illuminate\Pagination\Paginator;

class ProductController extends Controller
{
    public function getCategories(Request $request) {
        $fields = $request->get('fields', 'categories.*');
        if($fields != 'categories.*'){
            $fields = explode(',',$fields);
        }
        $categories = Category::select($fields)->with('branch')->with('company')
                            ->leftJoin('companies', 'categories.company_id', 'companies.id')
                            ->leftJoin('branches', 'categories.branch_id', 'branches.id');

        if(!empty($request->searchString)) {
            $categories = $categories->where('categoryName', 'LIKE', '%'.$request->searchString.'%');
        }

        if(!empty($request->status)) {
            $categories = $categories->where('isActive', ($request->status == 'in-active')?false:true);
        }

        if(!empty($request->companyId)) {
            $categories = $categories->where('categories.company_id', $request->companyId);
        }

        if(!empty($request->branchId)) {
            $categories = $categories->where('categories.branch_id', $request->branchId);
        }

        if(!empty($request->orderCol) && !empty($request->orderType)) {

            if($request->orderCol === 'branch') {
                $categories = $categories->orderBy('branches.branchTitle', $request->orderType);
            }else if($request->orderCol === 'company') {
                $categories = $categories->orderBy('companies.companyName', $request->orderType);
            }else {
                $categories = $categories->orderBy($request->orderCol, $request->orderType);
            }
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

    public function updateCategory(Request $request) {
        return \DB::transaction(function() use($request) {
            try {
                if(empty($request->id)) {
                    $category = new Category();
                }else {
                    $category = Category::find($request->id);
                }
                $category->categoryName = $request->categoryName;
                $category->description = $request->description;
                $category->branch_id = $request->branch_id;
                $category->isActive = $request->isActive ?? false;
                if(!empty($request->image)) {
                    $data = $request->image;
                    $base64_str = substr($data, strpos($data, ",")+1);
                    $image = base64_decode($base64_str);
                    $png_url = "cat-".time().mt_rand(1000, 9999).".png";
                    $path = '/img/categories/' . $png_url;
                    \Storage::disk('public')->put($path, $image);
                    $category->featuredImage = '/uploads'.$path;
                }
                $category->isSync = false;
                $category->save();
                return ['data' => $category, 'msg'=> "Category updated successfully"];
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not update category data', 'error'=>$e->getMessage()], 404);
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
                    $category->isSync = false;
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
        $products = Product::select($fields)->with('branch')->with('advancedPricing');



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

        if(!empty($request->branch_id)) {
            $products = $products->where('products.branch_id', $request->branch_id);
        }

        if(!empty($request->company_id)) {
            $products = $products->where('products.company_id', $request->company_id);
        }
        if(!empty($request->stockStatus)) {
            $products = $products->where('isOutOfStock', ($request->stockStatus == 'in-stock')?false:true);
        }
        if(!empty($request->orderCol) && !empty($request->orderType)) {
            $products = $products->orderBy($request->orderCol, $request->orderType);
        }
        $currentPage = $request->pageNumber;
        if(!empty($currentPage)){

            $products = $products->with('categories');

            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            return $products->paginate(10);
        }else {
            return $products->get();
        }
    }

    public function getProductDetail(Request $request, $id) {
        return Product::with('branch')->with('categories')->with('advancedPricing')->find($id);
    }

    public function updateProduct(Request $request) {
        return \DB::transaction(function() use($request) {
            try {
                if(!empty($request->id)) {
                    $product = Product::find($request->id);
                }else {
                    $product = new Product();
                }
                $product->productNumber = $request->productNumber;
                $product->productName = $request->productName;
                $product->description = $request->description;
                $product->price = $request->price;
                if(!empty($request->image)) {
                    $data = $request->image;
                    $base64_str = substr($data, strpos($data, ",")+1);
                    $image = base64_decode($base64_str);
                    $png_url = "user-".time().".png";
                    $path = '/img/products/' . $png_url;
                    \Storage::disk('public')->put($path, $image);
                    $product->featuredImage = '/uploads'.$path;
                }
                $product->taxPercent = $request->taxPercent;
                $product->packagingCharges = $request->packagingCharges;
                $product->isActive = $request->isActive ?? true;
                $product->isOutOfStock = $request->isOutOfStock ?? true;
                $product->isVeg = $request->isVeg ?? true;
                $product->branch_id = $request->branch_id;
                $product->kitchen_id = $request->kitchen_id;
                $product->isAdvancedPricing = $request->isAdvancedPricing ?? false;
                $categories = ($request->categories == "")?[]:$request->categories;

                $product->isSync = false;
                $product->save();

                if($product->isAdvancedPricing) {
                    foreach($request->pricingGroups as $group) {
                        if(!empty($group['deletedFlag']) && $group['deletedFlag'] == 'true') {
                            $pricing = ProductAdvancedPricing::find($group['id']);
                            $pricing->delete();
                        }else {
                            if(!empty($group['title']) && !empty($group['price'])) {
                                if(empty($group['id'])){
                                    $pricing = new ProductAdvancedPricing();
                                }else {
                                    $pricing = ProductAdvancedPricing::find($group['id']);
                                }
                                $pricing->productId = $product->id;
                                $pricing->title = $group['title'];
                                $pricing->price = $group['price'];
                                $pricing->isSync = false;
                                $pricing->save();
                            }
                        }
                    }
                }

                $product->categories()->sync($categories);
                return ['data' => $product, 'msg'=> "Product updated successfully"];
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not update product data, please check for duplicates', 'error' => $e->getMessage()], 404);
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
                    $product->isSync = false;
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


    public function getCategoryGroupedProduct(Request $request) {
        $categories = Category::where('isActive', true)->get();
        foreach($categories as $category) {
            $category['products'] = Product::leftJoin('product_categories', 'product_categories.product_id', 'products.id')->with('advancedPricing')
                    ->where('isActive', true)
                    ->where('product_categories.category_id', $category->id)->distinct()->get();
        }


        $otherProducts = Product::join('product_categories', 'product_categories.product_id', '=', 'products.id', 'left outer')->with('advancedPricing')->where('product_categories.product_id', NULL)->get();
        $categories[] = [
            'id' => 'other',
            'categoryName' => 'Others',
            'featuredImage' => '',
            'products' => $otherProducts
        ];
        return $categories;
    }


    //product combos

    public function getProductCombos(Request $request) {
        $fields = $request->get('fields', '*');
        if($fields != '*'){
            $fields = explode(',',$fields);
        }
        $productCombos = ProductCombo::select($fields)->with('branch');



        if(!empty($request->searchString)) {
            $productCombos = $productCombos->where(function($q) use ($request) {
                $q->where('comboTitle', 'LIKE', '%'.$request->searchString.'%')
                    ->orWhere('description', 'LIKE', '%'.$request->searchString.'%');
            });
        }
        if(!empty($request->status)) {
            $productCombos = $productCombos->where('isActive', ($request->status == 'in-active')?false:true);
        }

        if(!empty($request->branch_id)) {
            $productCombos = $productCombos->where('product_combos.branch_id', $request->branch_id);
        }

        if(!empty($request->company_id)) {
            $productCombos = $productCombos->where('product_combos.company_id', $request->company_id);
        }
        if(!empty($request->orderCol) && !empty($request->orderType)) {
            $productCombos = $productCombos->orderBy($request->orderCol, $request->orderType);
        }
        $currentPage = $request->pageNumber;
        if(!empty($currentPage)){


            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            return $productCombos->paginate(10);
        }else {
            return $productCombos->get();
        }
    }

    public function getProductComboDetail(Request $request, $id) {
        return ProductCombo::with('branch')->with('items')->find($id);
    }
    public function updateProductCombo(Request $request) {

        return \DB::transaction(function() use($request) {
            try {
                if(!empty($request->id)) {
                    $productCombo = ProductCombo::find($request->id);
                }else {
                    $productCombo = new ProductCombo();
                }
                if(!empty($request->image)) {
                    $data = $request->image;
                    $base64_str = substr($data, strpos($data, ",")+1);
                    $image = base64_decode($base64_str);
                    $png_url = "user-".time().".png";
                    $path = '/img/product-combos/' . $png_url;
                    \Storage::disk('public')->put($path, $image);
                    $productCombo->featuredImage = '/uploads'.$path;
                }
                $productCombo->isActive = $request->isActive ?? true;
                $productCombo->comboTitle = $request->comboTitle;
                $productCombo->description = $request->description;
                $productCombo->packagingCharges = $request->packagingCharges;
                $productCombo->branch_id = $request->branch_id;


                $productCombo->isSync = false;
                $productCombo->save();


                $totalComboAmount=0;

                foreach($request->items as $item) {
                    if($item['deletedFlag']) {
                        $comboItem = ProductComboItem::find($item['id']);
                        $comboItem->delete();
                    }
                    else if(!empty($item['quantity']) && !empty($item['productId'])){
                        if(empty($item['id'])) {
                            $comboItem = new ProductComboItem();
                        }else {
                            $comboItem = ProductComboItem::find($item['id']);
                        }
                        $product = Product::find( $item['productId']);
                        $comboItem->quantity = (int)$item['quantity'];
                        $comboItem->product_id = $product->id;
                        $comboItem->combo_id = $productCombo->id;
                        $comboItem->price = (float)$item['price'];

                        if($product->isAdvancedPricing) {
                            $pricing = ProductAdvancedPricing::find($item['advancedPriceId']);
                            if($pricing instanceof ProductAdvancedPricing) {
                                $comboItem->advancedPriceId = $pricing->id;
                                $comboItem->advancedPriceTitle = $pricing->title;
                            }
                        }else {
                            $comboItem->advancedPriceId = null;
                            $comboItem->advancedPriceTitle = null;
                        }
                        $totalPrice = $comboItem->quantity * $comboItem->price;
                        $comboItem->subTotal = $totalPrice;
                        $totalComboAmount = $totalComboAmount + $totalPrice;
                        $comboItem->isSync = false;
                        $comboItem->save();
                    }
                }
                $productCombo->comboTotal = $totalComboAmount;
                $productCombo->save();

                return ['data' => $productCombo, 'msg'=> "Product Combo updated successfully"];
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not update product data, please check for duplicates', 'error' => $e->getMessage()], 404);
            }
        });
    }

    public function changeProductComboStatus(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $productCombo = ProductCombo::find($id);
                if($productCombo instanceof ProductCombo) {
                    $productCombo->isActive = $request->isActive;
                    $productCombo->isSync = false;
                    $productCombo->save();
                    return ['data' => $productCombo, 'msg'=> "Product combo status updated successfully"];
                }else {
                    return response()->json(['msg' => 'Product combo Does not exist'], 404);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Product combo status can not changed'], 500);
            }
        });
    }

    public function deleteProductCombo(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $productCombo = ProductCombo::find($id);
                if($productCombo instanceof ProductCombo) {
                    $productCombo->delete();
                    return ['data' => $productCombo, 'msg'=> "Product combo deleted successfully"];
                }else {
                    return response()->json(['msg' => 'Product combo Does not exist'], 400);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not delete product combo', 'error'=> $e], 400);
            }
        });
    }
}
