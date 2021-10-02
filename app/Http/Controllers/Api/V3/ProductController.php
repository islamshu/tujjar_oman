<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Resources\V3\FlashDealResource;
use App\Http\Resources\V3\ProductResource;
use App\Http\Resources\V3\ProductDetailResource;
use App\Http\Resources\V3\SearchProductResource;
use App\Models\V3\FlashDeal;
use App\Models\V3\FlashDealProduct;
use App\Models\V3\Product;
use App\Models\V3\Color;
use App\Models\V3\Upload;
use App\Models\V3\ProductStock;
use Illuminate\Http\Request;
use App\Utility\CategoryUtility;
use Illuminate\Support\Str;
use App\Http\Controllers\Api\BaseController as BaseController;

class ProductController extends BaseController
{
    public function index()
    {
        $products['data'] = Product::where('published', 1)->latest()->get();
        if ($products['data']->count() > 0) {
            $products['data'] = ProductResource::collection($products['data']);
        }
        return $this->sendResponse($products, translate('products'));
    }

    public function show($id)
    {
        $product['data'] = Product::where('id',$id)->get();
        if ($product['data']) {
            $product['data'] = ProductDetailResource::collection($product['data']);
        }
        return $this->sendResponse($product, translate('this is product'));
    }

    public function admin()
    {
        $products['data'] = Product::where('published', 1)->where('added_by', 'admin')->latest()->get();
        if ($products['data']->count() > 0) {
            $products['data'] = ProductResource::collection($products['data']);
        }
        return $this->sendResponse($products, translate('admin products'));
    }

    public function seller()
    {
        $products['data'] = Product::where('published', 1)->where('added_by', 'seller')->latest()->get();
        if ($products['data']->count() > 0) {
            $products['data'] = ProductResource::collection($products['data']);
        }
        return $this->sendResponse($products, translate('seller products'));
    }

    public function category($id)
    {
        $category_ids = CategoryUtility::children_ids($id);
        $category_ids[] = $id;
        $cat['data'] = ProductResource::collection(Product::where('published', 1)->whereIn('category_id', $category_ids)->where(function ($qq){
            $qq->where('added_by','admin')->orWhereHas('user', function ($query) {
                $query->whereHas('seller', function ($q) {
                    $q->where('verification_status',1);
                });
            });
        })->latest()->get());
        return $this->sendResponse($cat, translate('categories products'));
    }

    public function subCategory($id)
    {
        $cat['data'] = [];
        $category_ids = CategoryUtility::children_ids($id);
        if ($category_ids) {
            $category_ids[] = $id;
            $products = Product::where('published', 1)->whereIn('category_id', $category_ids)->latest()->get();
            if ($products->count() > 0) {
                $cat['data'] = new ProductResource($products);
            }
        }
        return $this->sendResponse($cat, translate('subcategories products'));
    }

    public function subSubCategory($id)
    {
        $cat['data'] = [];
        $category_ids = CategoryUtility::children_ids($id);
        if ($category_ids) {
            $category_ids[] = $id;
            $products = Product::where('published', 1)->whereIn('category_id', $category_ids)->latest()->get();
            if ($products->count() > 0) {
                $cat['data'] = new ProductResource($products);
            }
        }
        return $this->sendResponse($cat, translate('subSubCategory products'));
    }

    public function brand($id)
    {
        $products['data'] = Product::where('published', 1)->where('brand_id', $id)->latest()->get();
        if ($products['data']->count() > 0) {
            $products['data'] = new ProductResource($products['data']);
        }
        return $this->sendResponse($products, translate('brand products'));
    }

    public function todaysDeal()
    {
        $products['data'] = Product::where('published', 1)->where('todays_deal', '1')->where(function ($qq){
            $qq->where('added_by','admin')->orWhereHas('user', function ($query) {
                $query->whereHas('seller', function ($q) {
                    $q->where('verification_status',1);
                });
            });
        })->latest()->get();
        if ($products['data']->count() > 0) {
            $products['data'] = ProductResource::collection($products['data']);
        }
        return $this->sendResponse($products, translate('today deals'));
    }

    public function flashDeal()
    {
        $flashes = FlashDeal::where('status', 1)->where('featured', 1)->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();
        if ($flashes->count() > 0)
            $flashes = new FlashDealResource($flashes->first());
        return $this->sendResponse($flashes, translate('flash deals'));
    }

    public function featured()
    {
        $fetured['data'] = Product::where('published', 1)->where('featured', 1)->where(function ($qq){
            $qq->where('added_by','admin')->orWhereHas('user', function ($query) {
                $query->whereHas('seller', function ($q) {
                    $q->where('verification_status',1);
                });
            });
        })->latest()->get();
        if ($fetured['data']) {
            $fetured['data'] = ProductResource::collection($fetured['data']);
        }
        return $this->sendResponse($fetured, translate('featured'));
    }

    public function bestSeller()
    {
        $best['data'] = Product::where('published', 1)->where(function ($qq){
            $qq->where('added_by','admin')->orWhereHas('user', function ($query) {
                $query->whereHas('seller', function ($q) {
                    $q->where('verification_status',1);
                });
            });
        })->orderBy('num_of_sale', 'desc')->limit(20)->get();
        if ($best['data']) {
            $best['data'] = ProductResource::collection($best['data']);
        }
        return $this->sendResponse($best, translate('best seller'));
    }

    public function related($id)
    {
        $product = Product::find($id);
        $related['data'] = ProductResource::collection(Product::where('published', 1)->where('category_id', $product->category_id)->where('id', '!=', $id)->limit(10)->get());
        return $this->sendResponse($related, translate('related product'));
    }

    public function topFromSeller($id)
    {
        $product = Product::find($id);
        $related['data'] = ProductResource::collection(Product::where('published', 1)->where('user_id', $product->user_id)->orderBy('num_of_sale', 'desc')->limit(4)->get());
        return $this->sendResponse($related, translate('topFromSeller product'));
    }

    public function search(Request $request)
    {
        $collection = array();
        $key = $request->key;
        $scope = $request->scope;
        $products = Product::whereIn('user_id', verified_sellers_id())->where('published', 1)->Where('name_ar', 'like', "%{$key}%")->orWhere('name', 'like', "%{$key}%")->Where('tags', 'like', "%{$key}%");
        if ($products->count() > 0) {
            switch ($scope) {
                case 'price_low_to_high':
                    $collection['data'] = SearchProductResource::collection($products->orderBy('unit_price', 'asc')->get());
                    return $this->sendResponse($collection, translate('price_low_to_high'));
                case 'price_high_to_low':
                    $collection['data'] = SearchProductResource::collection($products->orderBy('unit_price', 'desc')->get());
                    return $this->sendResponse($collection, translate('price_high_to_low'));
                case 'new_arrival':
                    $collection['data'] = SearchProductResource::collection($products->orderBy('created_at', 'desc')->get());
                    return $this->sendResponse($collection, translate('new_arrival'));
                case 'popularity':
                    $collection['data'] = SearchProductResource::collection($products->orderBy('num_of_sale', 'desc')->get());
                    return $this->sendResponse($collection, translate('popularity'));
                case 'top_rated':
                    $collection['data'] = SearchProductResource::collection($products->orderBy('rating', 'desc')->get());
                    return $this->sendResponse($collection, translate('top_rated'));
                default:
                    $collection['data'] = SearchProductResource::collection($products->orderBy('num_of_sale', 'desc')->get());
                    return $this->sendResponse($collection, translate('Search'));
            }
            $collection['key'] = $key;
            $collection['scope'] = $scope;
        }
        return $this->sendResponse($collection, translate('Search'));
    }

    public function variantPrice(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $str = '';
        if ($request->has('color')) {
            $data['color'] = $request['color'];
            $str = Color::where('code', $request['color'])->first()->name;
        }
        foreach (json_decode($request->choice) as $option) {
            $str .= $str != '' ? '-' . str_replace(' ', '', $option->name) : str_replace(' ', '', $option->name);
        }
        if ($str != null && $product->variant_product) {
            $product_stock = $product->stocks->where('variant', $str)->first();
            $price = $product_stock->price;
            $stockQuantity = $product_stock->qty;
        } else {
            $price = $product->unit_price;
            $stockQuantity = $product->current_stock;
        }
        $flash_deals = FlashDeal::where('status', 1)->get();
        $inFlashDeal = false;
        foreach ($flash_deals as $key => $flash_deal) {
            if ($flash_deal != null && $flash_deal->status == 1 && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date && FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first() != null) {
                $flash_deal_product = FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first();
                if ($flash_deal_product->discount_type == 'percent') {
                    $price -= ($price * $flash_deal_product->discount) / 100;
                } elseif ($flash_deal_product->discount_type == 'amount') {
                    $price -= $flash_deal_product->discount;
                }
                $inFlashDeal = true;
                break;
            }
        }
        if (!$inFlashDeal) {
            if ($product->discount_type == 'percent')
                $price -= ($price * $product->discount) / 100;
            elseif ($product->discount_type == 'amount')
                $price -= $product->discount;
        }
        if ($product->tax_type == 'percent')
            $price += ($price * $product->tax) / 100;
        elseif ($product->tax_type == 'amount')
            $price += $product->tax;
        return response()->json([
            'product_id' => $product->id,
            'variant' => $str,
            'price' => (double)$price,
            'in_stock' => $stockQuantity < 1 ? false : true
        ]);
    }

    public function home()
    {
        $data['data'] = ProductResource::collection(Product::inRandomOrder()->take(50)->get());
        return $data;
    }

    public function seller_product($atts, Request $request)
    {
        if ($atts == 'create') {
            $product = new Product;
            $product->name = $request->name_en;
            $product->name_ar = $request->name_ar;
            $product->added_by = 'seller';
            $product->user_id = auth('api')->user()->id;
            $product->category_id = $request->category_id;
            $product->brand_id = $request->brand_id;
            $product->current_stock = $request->current_stock;
            if ($request->hasFile('photos')) {
                foreach ($request->photos as $imdex1 => $img) {
                    $upload = new Upload;
                    $upload->file_original_name = null;
                    $arr = explode('.', $img->getClientOriginalName());
                    for ($i = 0; $i < count($arr) - 1; $i++) {
                        if ($i == 0)
                            $upload->file_original_name .= $arr[$i];
                        else
                            $upload->file_original_name .= "." . $arr[$i];
                    }
                    $upload->file_name = $img->store('uploads/all');
                    $upload->user_id = auth('api')->user()->id;
                    $upload->extension = strtolower($img->getClientOriginalExtension());
                    if (isset($type[$upload->extension]))
                        $upload->type = $type[$upload->extension];
                    else
                        $upload->type = "others";
                    $upload->file_size = $img->getSize();
                    $upload->save();
                    if ($imdex1 == 0)
                        $product->thumbnail_img = $upload->id;
                    $arrr[] = $upload->id;
                }
                $arrt = json_encode($arrr);
                $array = str_replace('[', '', $arrt);
                $array1 = str_replace(']', '', $array);
                $product->photos = $array1;
            }
            $product->unit = $request->unit;
            $product->min_qty = $request->min_qty;
            $product->description = $request->description_en;
            $product->description_ar = $request->description_ar;
            $product->unit_price = $request->unit_price;
            $product->purchase_price = $request->purchase_price;
            $product->tax = get_setting('tax');
            $product->tax_type = get_setting('tax_type');
            $product->discount = $request->discount;
            $product->discount_type = $request->discount_type;
            $product->meta_title = $product->name;
            $product->meta_description = $product->description;
            $product->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name_en)) . '-' . Str::random(5);
            if ($request->colors_id != null)
                $product->colors = json_encode($request->colors_id);
            else {
                $colorss = array();
                $product->colors = json_encode($colorss);
            }
            $choice_options = array();
            if ($request->size != null || $request->fabric != null) {
                if ($request->has('size')) {
                    $item['attribute_id'] = 1;
                    $data = array();
                    foreach ($request->size as $key => $eachValue) {
                        array_push($data, $eachValue);
                    }
                    $item['values'] = $data;
                    array_push($choice_options, $item);
                }
                if ($request->has('fabric')) {
                    $item['attribute_id'] = 2;
                    $data = array();
                    foreach ($request->fabric as $key => $eachValueq) {
                        array_push($data, $eachValueq);
                    }
                    $item['values'] = $data;
                    array_push($choice_options, $item);
                }
            } else
                $choice_options = array();
            $product->choice_options = json_encode($choice_options);
            $product->save();
            $options = array();
            if ($request->has('colors_id'))
                array_push($options, $request->colors_id);
            if ($request->has('size') || $request->has('fabric')) {
                if ($request->has('size')) {
                    $data = array();
                    foreach ($request->size as $key => $item) {
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
                if ($request->has('fabric')) {
                    $data = array();
                    foreach ($request->fabric as $key => $item) {
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
                $arryee = [];
                if ($request->size != null)
                    array_push($arryee, "1");
                if ($request->fabric != null)
                    array_push($arryee, "2");
                $product->attributes = json_encode($arryee);
            } else
                $product->attributes = array();
            $combinations = combinations($options);
            if (count($combinations[0]) > 0) {
                $product->variant_product = 1;
                foreach ($combinations as $key => $combination) {
                    $str = '';
                    foreach ($combination as $key => $item) {
                        if ($key > 0)
                            $str .= '-' . str_replace(' ', '', $item);
                        else {
                            if ($request->has('colors_id')) {
                                $color_name = \App\Color::where('code', $item)->first()->name;
                                $str .= $color_name;
                            } else {
                                $str .= str_replace(' ', '', $item);
                            }
                        }
                    }
                    $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                    if ($product_stock == null) {
                        $product_stock = new ProductStock;
                        $product_stock->product_id = $product->id;
                    }
                    $product_stock->variant = $str;
                    $product_stock->price = $request->purchase_price;
                    $product_stock->sku = $request['sku_' . str_replace('.', '_', $str)];
                    $product_stock->qty = $request->current_stock;
                    $product_stock->save();
                }
            } else {
                $product_stock = new ProductStock;
                $product_stock->product_id = $product->id;
                $product_stock->price = $request->unit_price;
                $product_stock->qty = $request->current_stock;
                $product_stock->save();
            }
            if ($product->save())
                return $this->sendResponse($product, translate('products created Successfully.'));
            else
                return $this->sendError(translate('error occer'));
        } elseif ($atts == 'update') {
            $product = Product::find($request->product_id);
            if (!$product)
                return $this->sendError(translate('no Product'));
            $product->name = $request->name_en;
            $product->name_ar = $request->name_ar;
            $product->added_by = 'seller';
            $product->user_id = auth('api')->user()->id;
            $product->category_id = $request->category_id;
            $product->brand_id = $request->brand_id;
            $product->current_stock = $request->current_stock;
            if ($request->hasFile('photos')) {
                foreach ($request->photos as $imdex1 => $img) {
                    $upload = new Upload;
                    $upload->file_original_name = null;
                    $arr = explode('.', $img->getClientOriginalName());
                    for ($i = 0; $i < count($arr) - 1; $i++) {
                        if ($i == 0)
                            $upload->file_original_name .= $arr[$i];
                        else
                            $upload->file_original_name .= "." . $arr[$i];
                    }
                    $upload->file_name = $img->store('uploads/all');
                    $upload->user_id = auth('api')->user()->id;
                    $upload->extension = strtolower($img->getClientOriginalExtension());
                    if (isset($type[$upload->extension]))
                        $upload->type = $type[$upload->extension];
                    else
                        $upload->type = "others";
                    $upload->file_size = $img->getSize();

                    $upload->save();
                    if ($imdex1 == 0)
                        $product->thumbnail_img = $upload->id;
                    $arrr[] = $upload->id;
                }
                $arrt = json_encode($arrr);
                $array = str_replace('[', '', $arrt);
                $array1 = str_replace(']', '', $array);
                $product->photos = $array1;
            }
            $product->unit = $request->unit;
            $product->min_qty = $request->min_qty;
            $product->description = $request->description_en;
            $product->description_ar = $request->description_ar;
            $product->unit_price = $request->unit_price;
            $product->purchase_price = $request->purchase_price;
            $product->tax = get_setting('tax');
            $product->tax_type = get_setting('tax_type');
            $product->discount = $request->discount;
            $product->discount_type = $request->discount_type;
            $product->meta_title = $product->name;
            $product->meta_description = $product->description;
            if ($request->colors_id != null)
                $product->colors = json_encode($request->colors_id);
            else {
                $colorss = array();
                $product->colors = json_encode($colorss);
            }
            $choice_options = array();
            if ($request->size != null || $request->fabric != null) {
                if ($request->has('size')) {
                    $item['attribute_id'] = 1;
                    $data = array();
                    foreach ($request->size as $key => $eachValue) {
                        array_push($data, $eachValue);
                    }
                    $item['values'] = $data;
                    array_push($choice_options, $item);
                }
                if ($request->has('fabric')) {
                    $item['attribute_id'] = 2;
                    $data = array();
                    foreach ($request->fabric as $key => $eachValueq) {
                        array_push($data, $eachValueq);
                    }
                    $item['values'] = $data;
                    array_push($choice_options, $item);
                }
            } else
                $choice_options = array();
            $product->choice_options = json_encode($choice_options);
            $product->save();
            $options = array();
            if ($request->has('colors_id'))
                array_push($options, $request->colors_id);
            if ($request->has('size') || $request->has('fabric')) {
                if ($request->has('size')) {
                    $data = array();
                    foreach ($request->size as $key => $item) {
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
                if ($request->has('fabric')) {
                    $data = array();
                    foreach ($request->fabric as $key => $item) {
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
                $arryee = [];
                if ($request->size != null)
                    array_push($arryee, "1");
                if ($request->fabric != null)
                    array_push($arryee, "2");
                $product->attributes = json_encode($arryee);
            } else
                $product->attributes = array();
            $combinations = combinations($options);
            if (count($combinations[0]) > 0) {
                $product->variant_product = 1;
                foreach ($combinations as $key => $combination) {
                    $str = '';
                    foreach ($combination as $key => $item) {
                        if ($key > 0)
                            $str .= '-' . str_replace(' ', '', $item);
                        else {
                            if ($request->has('colors_id')) {
                                $color_name = \App\Color::where('code', $item)->first()->name;
                                $str .= $color_name;
                            } else
                                $str .= str_replace(' ', '', $item);
                        }
                    }
                    $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                    if ($product_stock == null) {
                        $product_stock = new ProductStock;
                        $product_stock->product_id = $product->id;
                    }
                    $product_stock->variant = $str;
                    $product_stock->price = $request->purchase_price;
                    $product_stock->sku = $request['sku_' . str_replace('.', '_', $str)];
                    $product_stock->qty = $request->current_stock;
                    $product_stock->save();
                }
            } else {
                $product_stock = new ProductStock;
                $product_stock->product_id = $product->id;
                $product_stock->price = $request->unit_price;
                $product_stock->qty = $request->current_stock;
                $product_stock->save();
            }
            if ($product->save())
                return $this->sendResponse($product, translate('products created Successfully.'));
            else
                return $this->sendError(translate('error occer'));
        }
    }
}
