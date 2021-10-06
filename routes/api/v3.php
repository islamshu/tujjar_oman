<?php

Route::prefix('v3/auth')->namespace('Api\V3')->name('v3.')->middleware(['changeLanguage','header_request_env'])->group(function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
    Route::post('password/create', 'PasswordResetController@create');
    Route::post('password/reset', 'PasswordResetController@reset');
    Route::middleware('auth:api')->group(function () {
        Route::get('user', 'AuthController@user');
        Route::post('password/change', 'PasswordResetController@change');
    });
});

Route::prefix('v3')->namespace('Api\V3')->name('v3.')->middleware(['changeLanguage','header_request_env'])->group(function () {
    Route::apiResource('banners', 'BannerController')->only('index');
    Route::get('brands/top', 'BrandController@top');
    Route::apiResource('brands', 'BrandController')->only('index');
    Route::apiResource('business-settings', 'BusinessSettingController')->only('index');
    Route::get('categories/featured', 'CategoryController@featured');
    Route::get('categories/home', 'CategoryController@home');
    Route::apiResource('categories', 'CategoryController')->only('index');
    Route::get('sub-categories/{id}', 'SubCategoryController@index')->name('subCategories.index');
    Route::apiResource('colors', 'ColorController')->only('index');
    Route::apiResource('currencies', 'CurrencyController')->only('index');
    Route::apiResource('general-settings', 'GeneralSettingController')->only('index');
    Route::get('get_whatsapp', 'GeneralSettingController@get_phone_for_site');
    Route::apiResource('home-categories', 'HomeCategoryController')->only('index');
    Route::get('user/{id}', 'AuthController@userinfo');
    Route::get('products/admin', 'ProductController@admin');
    Route::get('products/seller', 'ProductController@seller');
    Route::get('products/category/{id}', 'ProductController@category')->name('api.products.category');
    Route::get('products/sub-category/{id}', 'ProductController@subCategory')->name('products.subCategory');
    Route::get('products/sub-sub-category/{id}', 'ProductController@subSubCategory')->name('products.subSubCategory');
    Route::get('products/brand/{id}', 'ProductController@brand')->name('api.products.brand');
    Route::get('products/todays-deal', 'ProductController@todaysDeal');
    Route::get('products/flash-deal', 'ProductController@flashDeal')->name('api_flashdeals');
    Route::get('products/featured', 'ProductController@featured');
    Route::get('products/best-seller', 'ProductController@bestSeller');
    Route::get('products/related/{id}', 'ProductController@related')->name('products.related');
    Route::get('products/top-from-seller/{id}', 'ProductController@topFromSeller')->name('products.topFromSeller');
    Route::get('products/search', 'ProductController@search');
    Route::post('products/variant/price', 'ProductController@variantPrice');
    Route::get('products/home', 'ProductController@home');
    Route::apiResource('products', 'ProductController')->except(['store', 'update', 'destroy']);
    Route::get('reviews/product/{id}', 'ReviewController@index')->name('api.reviews.index');
    Route::post('shops/create', 'ShopController@create');
    Route::get('shops/details/{id}', 'ShopController@info')->name('shops.info');
    Route::get('shops/products/all/{id}', 'ShopController@allProducts')->name('shops.allProducts');
    Route::get('shops/products/top/{id}', 'ShopController@topSellingProducts')->name('shops.topSellingProducts');
    Route::get('shops/products/featured/{id}', 'ShopController@featuredProducts')->name('shops.featuredProducts');
    Route::get('shops/products/new/{id}', 'ShopController@newProducts')->name('shops.newProducts');
    Route::get('shops/brands/{id}', 'ShopController@brands')->name('shops.brands');
    Route::get('bestseller', 'ShopController@bestseller');
    Route::get('sellers', 'ShopController@seller');
    Route::apiResource('shops', 'ShopController')->only('index');
    Route::get('pages/{id}', 'SearchController@pages');
    Route::apiResource('sliders', 'SliderController')->only('index');
    Route::apiResource('settings', 'SettingsController')->only('index');
    Route::get('policies/seller', 'PolicyController@sellerPolicy')->name('policies.seller');
    Route::get('policies/support', 'PolicyController@supportPolicy')->name('policies.support');
    Route::get('policies/return', 'PolicyController@returnPolicy')->name('policies.return');
    Route::get('product/get_search/{att}', 'SearchController@index');
    Route::post('fillter_search', 'SearchController@fillter_search');
    Route::get('test_cokkies', 'SearchController@test_cokkies');
    Route::get('test_cokkies2', 'SearchController@test_cokkies2');
    Route::get('get_color_card', 'SellerController@get_color_card')->name('api.get_color_card');
    Route::get('get_governorate', 'ColorController@get_governorate')->name('api.get_governorate');
    Route::get('get_states/{id}', 'ColorController@get_states')->name('api.get_states');
    Route::get('get_all_size', 'SearchController@get_all_size')->name('api.get_all_size');
    Route::get('get_all_fabrics', 'SearchController@get_all_fabrics')->name('api.get_all_fabrics');
    Route::get('get_all_colors', 'SearchController@get_all_colors')->name('api.get_all_colors');
    Route::get('get_all_langs', 'SearchController@get_all_langs')->name('api.get_all_langs');
    Route::get('vendor_bakege', 'SearchController@vendor_bakege')->name('api.vendor_bakege');
    Route::get('carts', 'CartController@index');
    Route::get('get_count_cart', 'CartController@get_count_cart');
    Route::post('carts/add', 'CartController@add');
    Route::post('retrun_to_paid/{id}/{id2}', 'CartController@make_order_id')->name('api.make_order');
    Route::post('carts/change-quantity', 'CartController@changeQuantity');
    Route::apiResource('carts', 'CartController')->only('destroy');
});

Route::prefix('v3')->namespace('Api\V3')->name('v3.')->middleware(['is_login', 'changeLanguage','header_request_env'])->group(function () {
    Route::get('logout', 'AuthController@logout');
    Route::get('get_noification/{id}', 'GeneralSettingController@get_noification_single')->name('notfy_single');
    Route::get('notofication', 'GeneralSettingController@get_noification');
    Route::get('wallet/history', 'WalletController@walletRechargeHistory');
    Route::get('wallet/balance', 'WalletController@balance');
    Route::post('wallet/recharge', 'WalletController@recharge');
    Route::post('product/review', 'ReviewController@store');
    Route::get('user/info', 'UserController@info');
    Route::post('user/info/update', 'UserController@updateName');
    Route::post('user/info/change_image', 'UserController@change_image');
    Route::get('user/shipping/address', 'AddressController@addresses');
    Route::post('user/shipping/create', 'AddressController@createShippingAddress');
    Route::get('user/shipping/set_defullt_address/{id}', 'AddressController@set_defulf_address');
    Route::post('user/shipping/update/{id}', 'AddressController@updateShippingAddress');
    Route::get('user/shipping/delete/{id}', 'AddressController@deleteShippingAddress');
    Route::post('coupon/apply', 'CouponController@apply');
    Route::post('payments/pay/stripe', 'StripeController@processPayment');
    Route::post('payments/pay/paypal', 'PaypalController@processPayment');
    Route::post('payments/pay/wallet', 'WalletController@processPayment');
    Route::post('payments/pay/cod', 'PaymentController@cashOnDelivery');
    Route::get('wishlist', 'WishlistController@index');
    Route::post('addTowishlist/{id}', 'WishlistController@addtowishlist');
    Route::post('removeFormwishlist/{id}', 'WishlistController@removeFormwishlist');
    Route::post('wishlists/check-product', 'WishlistController@isProductInWishlist');
    Route::post('wishlists/store', 'WishlistController@store');
    Route::apiResource('wishlists', 'WishlistController')->except(['update', 'show']);
    Route::get('purchase-history/{id}', 'PurchaseHistoryController@index');
    Route::get('purchase-history-details/{id}', 'PurchaseHistoryDetailController@index')->name('purchaseHistory.details');
    Route::get('shop/user', 'ShopController@shopOfUser')->name('api.shop');
    Route::post('send-message', 'ConversationController@create');
    Route::get('get_meesage', 'ConversationController@get_meesage');
    Route::get('meesage/{id}', 'ConversationController@message_id')->name('api.message_id');
    Route::post('replay_message', 'ConversationController@replay');
    Route::get('club_points', 'ClubPointController@index');
    Route::post('club_points/convert/{id}', 'ClubPointController@convert_point_into_wallet_id')->name('api_convert_club');
    Route::post('compare/add', 'CompareController@create');
    Route::get('compare', 'CompareController@index');
    Route::get('compare_delete/{id}', 'CompareController@delete')->name('api.delete_compare');
    Route::get('compare/reset', 'CompareController@reste')->name('api.reset_compare');
    Route::post('send_ticket', 'SupportTicketController@store');
    Route::post('ticket_replies', 'SupportTicketController@ticket_replies');
    Route::get('tickets', 'SupportTicketController@index');
    Route::get('seller/dashboard', 'SellerController@home');
    Route::post('seller/createOrupdate/{attribute}', 'ProductController@seller_product')->name('api.create_product');
    Route::post('edit_store', 'SellerController@store_edit');
    Route::post('bank_setting', 'SellerController@bank_setting');
    Route::post('shop/product_published/{id}', 'ShopController@product_published')->name('shops.product_published');
    Route::post('shop/product_featured/{id}', 'ShopController@product_featured')->name('shops.product_featured');
    Route::post('shop/delete_product/{id}', 'ShopController@delete_product')->name('shops.delete_product');
    Route::get('shops/get_products', 'ShopController@allProductsLogin')->name('shops.allProductsLogin');
    Route::get('seller/orders', 'SellerController@get_orders')->name('api_order');
    Route::post('seller/get_orders_by_status', 'SellerController@get_orders_status');
    Route::get('seller/ordersDetails/{id}', 'SellerController@ordersDetails')->name('api.ordersDetails');
    Route::get('seller/orders_delete/{id}', 'SellerController@orders_delete')->name('api.orders_delete');
    Route::get('seller/product_review', 'SellerController@product_seller_review')->name('api.product_review');
    Route::get('seller/PurchaseHistory', 'SellerController@PurchaseHistory')->name('api.PurchaseHistory');
    Route::post('seller/Seller_card', 'SellerController@Seller_card')->name('api.Seller_card');
    Route::post('seller_withdrow', 'SellerController@seller_withdrow')->name('api.seller_withdrow');
    Route::get('Pending_Balance_for_seller', 'SellerController@Pending_Balance_for_seller')->name('api.Pending_Balance_for_seller');
    Route::get('seller_withdraw_requests', 'SellerController@seller_withdraw_requests')->name('api.seller_withdraw_requests');
    Route::get('seller/payments', 'SellerController@payments')->name('api.seller_payments');
    Route::post('seller/update_delivery_status', 'SellerController@update_delivery_status')->name('api.update_delivery_status');
    Route::post('seller/paid_to_be_vendor', 'ShopController@piad_for_vendor')->name('api.paid_to_be_vendor');
    Route::post('shop_update', 'ShopController@shop_update');
    Route::get('/sosaal', function () {
        return Share::load('http://www.example.com', 'Link description')->services('facebook', 'gplus', 'twitter');
    });
});