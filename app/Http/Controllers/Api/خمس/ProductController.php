<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductDetailCollection;
use App\Http\Resources\SearchProductCollection;
use App\Http\Resources\FlashDealCollection;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Color;
use Auth;
use App\Upload;
use App\ProductStock;
use Illuminate\Http\Request;
use App\Utility\CategoryUtility;
use Illuminate\Support\Str;

use App\Http\Controllers\Api\BaseController as BaseController;

class ProductController extends BaseController
{
    public function index()
    {
       $products=  new ProductCollection(Product::latest()->paginate(10));
         return $this->sendResponse($products,'products');
    }

    public function show($id)
    {
        $show= new ProductDetailCollection(Product::where('id', $id)->get());
         return $this->sendResponse($show,'this is product');
    }

    public function admin()
    {
        $admin= new ProductCollection(Product::where('added_by', 'admin')->latest()->paginate(10));
         return $this->sendResponse($admin,'admin products');
    }

    public function seller()
    {
        $seller= new ProductCollection(Product::where('added_by', 'seller')->latest()->paginate(10));
                 return $this->sendResponse($seller,'seller products');

    }

    public function category($id)
    {
        $category_ids = CategoryUtility::children_ids($id);
        $category_ids[] = $id;

        $cat = new ProductCollection(Product::whereIn('category_id', $category_ids)->latest()->paginate(10));
          return $this->sendResponse($cat,'categories products');
    }

    public function subCategory($id)
    {
        
        $category_ids = CategoryUtility::children_ids($id);
        $category_ids[] = $id;

        $cat= new ProductCollection(Product::whereIn('category_id', $category_ids)->latest()->paginate(10));
             return $this->sendResponse($cat,'subcategories products');
    }

    public function subSubCategory($id)
    {
        $category_ids = CategoryUtility::children_ids($id);
        $category_ids[] = $id;

       $cat= new ProductCollection(Product::whereIn('category_id', $category_ids)->latest()->paginate(10));
                     return $this->sendResponse($cat,'subSubCategory products');

    }

    public function brand($id)
    {
        $brand= new ProductCollection(Product::where('brand_id', $id)->latest()->paginate(10));
                             return $this->sendResponse($brand,'brand products');

    }

    public function todaysDeal()
    {
        $todays=  new ProductCollection(Product::where('todays_deal', 1)->latest()->get());
        return $this->sendResponse($todays,'today deals');
    }

    public function flashDeal()
    {
        $flash_deals = FlashDeal::where('status', 1)->where('featured', 1)->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();
     $flashes= new FlashDealCollection($flash_deals);
        return $this->sendResponse($flashes,'flash deals');
    }

    public function featured()
    {
        $fetured= new ProductCollection(Product::where('featured', 1)->latest()->get());
        return $this->sendResponse($fetured,'featured');

        
    }

    public function bestSeller()
    {
        $best= new ProductCollection(Product::orderBy('num_of_sale', 'desc')->limit(20)->get());
        return $this->sendResponse($best,'best seller');

    }

    public function related($id)
    {
        $product = Product::find($id);
        $related = new ProductCollection(Product::where('category_id', $product->category_id)->where('id', '!=', $id)->limit(10)->get());
                return $this->sendResponse($related,'related product');

    }

    public function topFromSeller($id)
    {
        $product = Product::find($id);
       $related= new ProductCollection(Product::where('user_id', $product->user_id)->orderBy('num_of_sale', 'desc')->limit(4)->get());
         return $this->sendResponse($related,'topFromSeller product');

    }

    public function search(Request $request)
    {
        $key = $request->key;
        $scope = $request->scope;
    // dd($scope);
        switch ($scope) {

            case 'price_low_to_high':
                // dd('dfdd');
                $collection = new SearchProductCollection(Product::where('name', 'like', "%{$key}%")->orWhere('name_ar', 'like', "%{$key}%")->orWhere('tags', 'like', "%{$key}%")->orderBy('unit_price', 'asc')->paginate(10));
                $collection->appends(['key' =>  $key, 'scope' => $scope]);
                         return $this->sendResponse($collection,'price_low_to_high');

                

            case 'price_high_to_low':
                $collection = new SearchProductCollection(Product::where('name', 'like', "%{$key}%")->orWhere('name_ar', 'like', "%{$key}%")->orWhere('tags', 'like', "%{$key}%")->orderBy('unit_price', 'desc')->paginate(10));
                $collection->appends(['key' =>  $key, 'scope' => $scope]);
                         return $this->sendResponse($collection,'price_high_to_low');

            case 'new_arrival':
                $collection = new SearchProductCollection(Product::where('name', 'like', "%{$key}%")->orWhere('name_ar', 'like', "%{$key}%")->orWhere('tags', 'like', "%{$key}%")->orderBy('created_at', 'desc')->paginate(10));
                $collection->appends(['key' =>  $key, 'scope' => $scope]);
                   return $this->sendResponse($collection,'new_arrival');
            case 'popularity':
                $collection = new SearchProductCollection(Product::where('name', 'like', "%{$key}%")->orWhere('name_ar', 'like', "%{$key}%")->orWhere('tags', 'like', "%{$key}%")->orderBy('num_of_sale', 'desc')->paginate(10));
                $collection->appends(['key' =>  $key, 'scope' => $scope]);
                           return $this->sendResponse($collection,'popularity');
            case 'top_rated':
                $collection = new SearchProductCollection(Product::where('name', 'like', "%{$key}%")->orWhere('name_ar', 'like', "%{$key}%")->orWhere('tags', 'like', "%{$key}%")->orderBy('rating', 'desc')->paginate(10));
                $collection->appends(['key' =>  $key, 'scope' => $scope]);
                   return $this->sendResponse($collection,'top_rated');

            // case 'category':
            //
            //     $categories = Category::select('id')->where('name', 'like', "%{$key}%")->get()->toArray();
            //     $collection = new SearchProductCollection(Product::where('category_id', $categories)->orderBy('num_of_sale', 'desc')->paginate(10));
            //     $collection->appends(['key' =>  $key, 'scope' => $scope]);
            //     return $collection;
            //
            // case 'brand':
            //
            //     $brands = Brand::select('id')->where('name', 'like', "%{$key}%")->get()->toArray();
            //     $collection = new SearchProductCollection(Product::where('brand_id', $brands)->orderBy('num_of_sale', 'desc')->paginate(10));
            //     $collection->appends(['key' =>  $key, 'scope' => $scope]);
            //     return $collection;
            //
            // case 'shop':
            //
            //     $shops = Shop::select('user_id')->where('name', 'like', "%{$key}%")->get()->toArray();
            //     $collection = new SearchProductCollection(Product::where('user_id', $shops)->orderBy('num_of_sale', 'desc')->paginate(10));
            //     $collection->appends(['key' =>  $key, 'scope' => $scope]);
            //     return $collection;

            default:
                $collection = new SearchProductCollection(Product::where('name', 'like', "%{$key}%")->orWhere('tags', 'like', "%{$key}%")->orderBy('num_of_sale', 'desc')->paginate(10));
                $collection->appends(['key' =>  $key, 'scope' => $scope]);
                   return $this->sendResponse($collection,'Search');
        }
    }

    public function variantPrice(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $str = '';
        $tax = 0;

        if ($request->has('color')) {
            $data['color'] = $request['color'];
            $str = Color::where('code', $request['color'])->first()->name;
        }

        foreach (json_decode($request->choice) as $option) {
            $str .= $str != '' ?  '-'.str_replace(' ', '', $option->name) : str_replace(' ', '', $option->name);
        }

        if($str != null && $product->variant_product){
            $product_stock = $product->stocks->where('variant', $str)->first();
            $price = $product_stock->price;
            $stockQuantity = $product_stock->qty;
        }
        else{
            $price = $product->unit_price;
            $stockQuantity = $product->current_stock;
        }

        //discount calculation
        $flash_deals = FlashDeal::where('status', 1)->get();
        $inFlashDeal = false;
        foreach ($flash_deals as $key => $flash_deal) {
            if ($flash_deal != null && $flash_deal->status == 1 && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date && FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first() != null) {
                $flash_deal_product = FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first();
                if($flash_deal_product->discount_type == 'percent'){
                    $price -= ($price*$flash_deal_product->discount)/100;
                }
                elseif($flash_deal_product->discount_type == 'amount'){
                    $price -= $flash_deal_product->discount;
                }
                $inFlashDeal = true;
                break;
            }
        }
        if (!$inFlashDeal) {
            if($product->discount_type == 'percent'){
                $price -= ($price*$product->discount)/100;
            }
            elseif($product->discount_type == 'amount'){
                $price -= $product->discount;
            }
        }

        if ($product->tax_type == 'percent') {
            $price += ($price*$product->tax) / 100;
        }
        elseif ($product->tax_type == 'amount') {
            $price += $product->tax;
        }

        return response()->json([
            'product_id' => $product->id,
            'variant' => $str,
            'price' => (double) $price,
            'in_stock' => $stockQuantity < 1 ? false : true
        ]);
    }

    public function home()
    {
        return new ProductCollection(Product::inRandomOrder()->take(50)->get());
    }
    
    public function seller_product($atts,Request $request){
    if($atts == 'create') {
        if($request->colors_id != null){
         $color= ($request->colors_id);
       $colors = json_encode($request->colors_id);   
        }
        else {
            $colors= array();
        }
       
   
        $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();

        $product = new Product;
        
        $product->name = $request->name_en;
          $product->name_ar = $request->name_ar;
        $product->added_by = 'seller';
        $product->user_id = Auth::user()->id;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->current_stock = $request->current_stock;
         if($request->hasFile('photos')){
            foreach($request->photos as $imdex1 =>$img){
            // dd( $request->photos->first)  ;
             $upload = new Upload;
            $upload->file_original_name = null;

            $arr = explode('.', $img->getClientOriginalName());

            for($i=0; $i < count($arr)-1; $i++){
                if($i == 0){
                    $upload->file_original_name .= $arr[$i];
                }
                else{
                    $upload->file_original_name .= ".".$arr[$i];
                }
            }

            $upload->file_name = $img->store('uploads/all');
            $upload->user_id = Auth::user()->id;
            $upload->extension = strtolower($img->getClientOriginalExtension());
            if(isset($type[$upload->extension])){
                $upload->type = $type[$upload->extension];
            }
            else{
                $upload->type = "others";
            }
            $upload->file_size = $img->getSize();
            
            $upload->save();
            if($imdex1 == 0){
                $product->thumbnail_img =$upload->id;
            }
            $arrr[]=$upload->id;
            
       }
        $arrt= json_encode($arrr);
        $array = str_replace('[','',$arrt);
        $array1=str_replace(']','',$array);
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



        $product->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name_en)).'-'.Str::random(5);

        if( $request->colors_id != null){
            $product->colors =json_encode($request->colors_id);
        }
        else {
            $colorss = array();
            $product->colors = json_encode($colorss);
        }
          $choice_options = array();
            if ($request->size != null || $request->fabric != null ){

      

        if($request->has('size')){

                $str = 'choice_options_1';

                $item['attribute_id'] = 1;

                $data = array();
                foreach ($request->size as $key => $eachValue) {
                    array_push($data, $eachValue);
                }

                $item['values'] = $data;
                
                array_push($choice_options, $item);
                
        }
         if($request->has('fabric')){

                $str = 'choice_options_2';

                $item['attribute_id'] = 2;

                $data = array();
                foreach ($request->fabric as $key => $eachValueq) {
                    array_push($data, $eachValueq);
                }

                $item['values'] = $data;
                array_push($choice_options, $item);
            }
                 $choice_options=   ($choice_options);

        }else{
        $choice_options=   array();


        }
        


        $product->choice_options =json_encode($choice_options);
        // dd($product->choice_options);

        $product->save();
      

        $options = array();
        if($request->has('colors_id')){
            $colors_active = 1;
          
            array_push($options, $request->colors_id);
        }
    if($request->has('size') || $request->has('fabric')){
        if($request->has('size')){
                $name = 'choice_options_1';
                $data = array();
                foreach ($request->size as $key =>$item ) {
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        
             if($request->has('fabric')){
                $name = 'choice_options_2';
                $data = array();
                foreach ($request->fabric as $key => $item) {
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
             $arryee=[];
            if($request->size != null ){
                                array_push($arryee, "1");
            }
             if($request->fabric != null ){
                                array_push($arryee,"2");
            }
            // dd();

             $product->attributes = json_encode($arryee);
    }else {
        $product->attributes = array();
    }
           
            //  dd($product->attributes);

                
            
        

        $combinations = combinations($options);
        
          
        if(count($combinations[0]) > 0){
            $product->variant_product = 1;
            foreach ($combinations as $key => $combination){
                $str = '';
                foreach ($combination as $key => $item){
                    if($key > 0 ){
                        $str .= '-'.str_replace(' ', '', $item);
                    }
                    else{
                        if($request->has('colors_id')){
                            // dd($item);
                            $color_name = \App\Color::where('code', $item)->first()->name;
                            $str .= $color_name;
                        }
                        else{
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }
                $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                if($product_stock == null){
                    $product_stock = new ProductStock;
                    $product_stock->product_id = $product->id;
                }

                $product_stock->variant = $str;
                $product_stock->price = $request->purchase_price;
                $product_stock->sku = $request['sku_'.str_replace('.', '_', $str)];
                $product_stock->qty = $request->current_stock;
                $product_stock->save();
            }
        }
        else{
            $product_stock = new ProductStock;
            $product_stock->product_id = $product->id;
            $product_stock->price = $request->unit_price;
            $product_stock->qty = $request->current_stock;
            $product_stock->save();
            
        }
     
        //combinations end

	    if($product->save()){
	              return $this->sendResponse($product , 'products created Successfully.');
  
	    }else{
	      	              return $this->sendError('error occer' );
  
	    }
	    
	    
	    
        
    }elseif($atts =='update'){
     if($request->colors_id != null){
         $color= ($request->colors_id);
       $colors = json_encode($request->colors_id);   
        }
        else {
            $colors= array();
        }
       
//   
        // $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();

        $product = Product::find($request->product_id) ;
        // dd($product);
        
        $product->name = $request->name_en;
          $product->name_ar = $request->name_ar;
        $product->added_by = 'seller';
        $product->user_id = Auth::user()->id;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->current_stock = $request->current_stock;
         if($request->hasFile('photos')){
            foreach($request->photos as $imdex1 =>$img){
            // dd( $request->photos->first)  ;
             $upload = new Upload;
            $upload->file_original_name = null;

            $arr = explode('.', $img->getClientOriginalName());

            for($i=0; $i < count($arr)-1; $i++){
                if($i == 0){
                    $upload->file_original_name .= $arr[$i];
                }
                else{
                    $upload->file_original_name .= ".".$arr[$i];
                }
            }

            $upload->file_name = $img->store('uploads/all');
            $upload->user_id = Auth::user()->id;
            $upload->extension = strtolower($img->getClientOriginalExtension());
            if(isset($type[$upload->extension])){
                $upload->type = $type[$upload->extension];
            }
            else{
                $upload->type = "others";
            }
            $upload->file_size = $img->getSize();
            
            $upload->save();
            if($imdex1 == 0){
                $product->thumbnail_img =$upload->id;
            }
            $arrr[]=$upload->id;
            
       }
        $arrt= json_encode($arrr);
        $array = str_replace('[','',$arrt);
        $array1=str_replace(']','',$array);
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



        // $product->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name_en)).'-'.Str::random(5);

        if( $request->colors_id != null){
            $product->colors =json_encode($request->colors_id);
        }
        else {
            $colorss = array();
            $product->colors = json_encode($colorss);
        }
          $choice_options = array();
            if ($request->size != null || $request->fabric != null ){

      

        if($request->has('size')){

                $str = 'choice_options_1';

                $item['attribute_id'] = 1;

                $data = array();
                foreach ($request->size as $key => $eachValue) {
                    array_push($data, $eachValue);
                }

                $item['values'] = $data;
                
                array_push($choice_options, $item);
                
        }
         if($request->has('fabric')){

                $str = 'choice_options_2';

                $item['attribute_id'] = 2;

                $data = array();
                foreach ($request->fabric as $key => $eachValueq) {
                    array_push($data, $eachValueq);
                }

                $item['values'] = $data;
                array_push($choice_options, $item);
            }
                 $choice_options=   ($choice_options);

        }else{
        $choice_options=   array();


        }
        


        $product->choice_options =json_encode($choice_options);
        // dd($product->choice_options);

        $product->save();
      

        $options = array();
        if($request->has('colors_id')){
            $colors_active = 1;
          
            array_push($options, $request->colors_id);
        }
    if($request->has('size') || $request->has('fabric')){
        if($request->has('size')){
                $name = 'choice_options_1';
                $data = array();
                foreach ($request->size as $key =>$item ) {
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        
             if($request->has('fabric')){
                $name = 'choice_options_2';
                $data = array();
                foreach ($request->fabric as $key => $item) {
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
             $arryee=[];
            if($request->size != null ){
                                array_push($arryee, "1");
            }
             if($request->fabric != null ){
                                array_push($arryee,"2");
            }
            // dd();

             $product->attributes = json_encode($arryee);
    }else {
        $product->attributes = array();
    }
           
            //  dd($product->attributes);

                
            
        

        $combinations = combinations($options);
        
          
        if(count($combinations[0]) > 0){
            $product->variant_product = 1;
            foreach ($combinations as $key => $combination){
                $str = '';
                foreach ($combination as $key => $item){
                    if($key > 0 ){
                        $str .= '-'.str_replace(' ', '', $item);
                    }
                    else{
                        if($request->has('colors_id')){
                            // dd($item);
                            $color_name = \App\Color::where('code', $item)->first()->name;
                            $str .= $color_name;
                        }
                        else{
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }
                $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                if($product_stock == null){
                    $product_stock = new ProductStock;
                    $product_stock->product_id = $product->id;
                }

                $product_stock->variant = $str;
                $product_stock->price = $request->purchase_price;
                $product_stock->sku = $request['sku_'.str_replace('.', '_', $str)];
                $product_stock->qty = $request->current_stock;
                $product_stock->save();
            }
        }
        else{
            $product_stock = new ProductStock;
            $product_stock->product_id = $product->id;
            $product_stock->price = $request->unit_price;
            $product_stock->qty = $request->current_stock;
            $product_stock->save();
            
        }
     
        //combinations end

	    if($product->save()){
	              return $this->sendResponse($product , 'products created Successfully.');
  
	    }else{
	      	              return $this->sendError('error occer' );
  
	    }
	    

    }
    }
  
    
    
    
}
