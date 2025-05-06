<?php

    $foodCategories = ['Asian Fusion', 'Bagels', 'Bakery', 'Bar', 'Barbeque', 'Breakfast', 'British',
        'Brunch', 'Buffets', 'Burgers', 'Cajun/Creole', 'Caribbean', 'Coffee/Espresso', 'Country Food', 'Cuban',
        'Deli', 'Doughnuts', 'Family Fare', 'Fast Food', 'Fine Dining', 'Food Trucks', 'French', 'German',
        'Gluten-free', 'Greek', 'Happy Hour', 'Hot Dogs', 'Ice Cream', 'Indian', 'Irish', 'Italian',
        'Japanese', 'Latin American', 'Live Entertainment', 'Mediterranean', 'Mexican', 'Nouvelle', 'Pancakes/Waffles', 'Pizza',
        'Polish', 'Sandwiches', 'Seafood', 'Soul Food', 'Soup & Salad', 'Southern', 'Spanish',
        'Sports Bar', 'Steaks', 'Sushi', 'Tapas', 'Thai', 'Vegan Friendly', 'Vegetarian'];

    $shoppingCategories = ['Antiques', 'Art Galleries', 'Arts & Crafts', 'Auction Houses', 'Baby Gear', 'Battery Stores',
        'Bespoke Clothing', 'Books, Mags, Music & Video', 'Brewing Supplies', 'Bridal', 'Cannabis Dispensaries', 'Clothing', 'Computers', 'Cosmetics & Beauty Supply', 'Customized Merchandise',
        'Department Stores', 'Discount Stores', 'Drones', 'Drugstores', 'Duty-Free Shops', 'Electronics', 'Eyeware & Opticians', 'Farming Equipment',
        'Fashion', 'Fireworks', 'Fitness/Exercise Equipment', 'Flea Markets', 'Flowers & Gifts', 'Gemstones & Minerals', 'Gold Buyers', 'Groceries', 'Guns & Ammo',
        'Head Shops', 'High Fidelity Audio Equipment', 'Hobby Shops', 'Home & Garden', 'Horse Equipment Shops', 'Jewelry', 'Knitting Supplies', 'Livestock Feed & Supply',
        'Luggage', 'Medical Supplies', 'Military Surplus', 'Mobile Phone Accessories', 'Mobile Phones', 'Motorcycle Gear', 'Musical Instruments & Teachers',
        'Office Equipment', 'Outlet Stores', 'Packing Supplies', 'Pawn Shops', 'Perfume', 'Photography Stores & Services', 'Pool & Billiards',
        'Pop-up Shops', 'Props', 'Public Markets', 'Religious Items', 'Safe Stores', 'Safety Equipment', 'Shopping Centers',
        'Souvenir Shops', 'Spiritual Shops', 'Sporting Goods', 'Tabletop Games', 'Teacher Supplies', 'Thrift Stores', 'Tobacco Shops',
        'Toy Stores', 'Trophy Shops', 'Uniforms', 'Used Bookstore', 'Vape Shops', 'Vitamins & Supplements', 'Watches',
        'Wholesale Stores', 'Wigs', 'Smoke Shops'];

    $eventCategories = ['Film', 'Arts & Theatre', 'Music', 'Sports', 'Miscellaneous', 'Nonticket'];

    return [
        'my_loc_x' => env('MY_LOC_X', '0.0'),
        'my_loc_y' => env('MY_LOC_Y', '0.0'),

        'my_address'  => env('MY_ADDRESS', '1175 NE. Miami Gardens Dr., Miami, FL 33179'),
        'my_city'     => env('MY_CITY', 'Miami'),
        'my_state'    => env('MY_STATE', 'Florida'),
        'my_country'  => env('MY_COUNTRY', 'USA'),
        'my_line_1'   => env('MY_LINE_1', '1175 NE. Miami Gardens Dr.'),
        'my_zip_code' => env('MY_ZIP_CODE', '33179'),

        'default_images_path'    => env('DEFAULT_IMAGES_PATH', 'https://api-demo.rpx.com/defaults/'),
        'background_images_path' => env('BACKGROUND_IMAGES_PATH', 'https://api-demo.rpx.com/backgrounds/'),
        'rewards_images_path'    => env('REWARDS_IMAGES_PATH', 'https://api-demo.rpx.com/rewards-media/images/'),
        'ad_images_path'         => env('AD_IMAGES_PATH', 'https://api-demo.rpx.com/ad-media/images/'),

        'business_pass_key' => env('RPX_BUSINESS_PASSKEY'),

        'my_business_categories_food'     => $foodCategories,
        'my_business_categories_shopping' => $shoppingCategories,
        'my_business_categories_events'   => $eventCategories,

        'rpx_front_end_ip' => env('RPX_FRONT_END_IP', 'https://rpx.com/'),

        'header_banner_product' => env('RPX_HEADER_AD_STRIPE_PRODUCT'),
        'header_banner_price'   => env('RPX_HEADER_AD_PRICE'),

        'sms' => [
            'queue' => env('RPX_QUEUE'),
        ],

        'featured_related_product' => env('RPX_FEATURED_AD_STRIPE_PRODUCT'),
        'featured_related_price'   => env('RPX_FEATURED_AD_PRICE'),

        'featured_related_product' => env('RPX_FOOTER_AD_STRIPE_PRODUCT'),
        'footer_banner_price'      => env('RPX_FOOTER_AD_PRICE'),

        'business_subscription_product' => env('RPX_BUSINESS_SUBSCRIPTION_PRODUCT'),
        // Legacy Price. My account still uses this one
        'business_subscription_price'   => env('RPX_BUSINESS_SUBSCRIPTION_PRICE'),

        // $199.99 monthly
        'business_subscription_price1'   => env('RPX_BUSINESS_SUBSCRIPTION_PRICE1'),
        // $299.99 monthly
        'business_subscription_price_1_2'   => env('RPX_BUSINESS_SUBSCRIPTION_PRICE_1_2'),
        // $399.99 monthly
        'business_subscription_price_2_2'   => env('RPX_BUSINESS_SUBSCRIPTION_PRICE_2_2'),
    ];
