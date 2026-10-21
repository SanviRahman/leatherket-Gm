<!DOCTYPE HTML>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="cache-control" content="max-age=604800" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{asset('images/favicon.ico')}}">

    <title>{{ Settings::get('site_name') }}</title>

    <script src="{{asset('assets/js/jquery-2.0.0.min.js')}}"></script>
    <script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
    <link href="{{asset('assets/css/bootstrap.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/fonts/fontawesome/css/all.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/ui.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/responsive.css')}}" rel="stylesheet" type="text/css"  media="only screen and (max-width: 1200px)"  />
    <link href="{{asset('assets/css/style.css')}}" rel="stylesheet" type="text/css" />
    <link type="text/css" href="{{ asset('assets/css/sweetalert2.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    {{-- Typography is defined globally in leatherket-typography.css (icons remain intact). --}}
    <style>
        .shipping-options { display:flex; flex-direction:column; gap:8px; }
        .shipping-option { display:flex; align-items:center; gap:8px; padding:10px 15px; border:1px solid #ccc; border-radius:8px; cursor:pointer; background-color:#f8f9fa; transition:background .2s ease,border .2s ease; }
        .shipping-option input[type="radio"] { accent-color:green; }
        .shipping-option.active { background-color:#28a745 !important; color:#fff; border-color:#28a745; font-weight:600; }
        .campaign-color-selector .btn-group-toggle,
        .campaign-size-selector .btn-group-toggle,
        .campaign-option-selector .btn-group-toggle {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 4px;
        }
        .campaign-color-selector .btn,
        .campaign-size-selector .btn,
        .campaign-option-selector .btn {
            flex: 0 0 auto;
            margin: 0 !important;
            white-space: nowrap;
        }

        .campaign-product-picker {
            border: 1px solid #e6e6e6;
            border-radius: 8px;
            background: #fff;
            padding: 14px;
            margin-bottom: 14px;
        }
        .campaign-product-picker-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .campaign-product-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .campaign-product-choice {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1 1 190px;
            min-height: 58px;
            padding: 10px 12px;
            margin: 0;
            border: 2px solid #d9dee5;
            border-radius: 8px;
            background: #fff;
            cursor: pointer;
            transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
        }
        .campaign-product-choice:hover {
            border-color: #28a745;
        }
        .campaign-product-choice.active {
            border-color: #28a745;
            background: #eefbf2;
            box-shadow: 0 0 0 1px rgba(40, 167, 69, .08);
        }
        .campaign-product-choice input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .campaign-product-check {
            width: 24px;
            height: 24px;
            border: 2px solid #adb5bd;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 24px;
            color: transparent;
            background: #fff;
        }
        .campaign-product-choice.active .campaign-product-check {
            border-color: #28a745;
            background: #28a745;
            color: #fff;
        }
        .campaign-product-meta {
            min-width: 0;
            flex: 1 1 auto;
        }
        .campaign-product-name {
            display: block;
            font-weight: 700;
            line-height: 1.2;
            word-break: break-word;
        }
        .campaign-product-price {
            display: block;
            margin-top: 4px;
            font-size: 15px;
            line-height: 1.2;
            font-weight: 700;
            color: #212529;
        }
        .campaign-product-state {
            display: inline-block;
            min-width: 58px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            color: #6c757d;
        }
        .campaign-product-choice.active .campaign-product-state {
            color: #218838;
        }

        @media (max-width: 767.98px) {
            .campaign-product-picker { padding: 10px; }
            .campaign-product-options {
                display: flex;
                flex-direction: column;
                flex-wrap: nowrap;
                gap: 8px;
            }
            .campaign-product-choice {
                flex: 0 0 auto;
                width: 100%;
                min-width: 100%;
                min-height: 58px;
                padding: 10px 10px;
                gap: 8px;
                box-sizing: border-box;
            }
            .campaign-product-check {
                width: 21px;
                height: 21px;
                flex-basis: 21px;
            }
            .campaign-product-name { font-size: 13px; }
            .campaign-product-price {
                font-size: 14px;
                font-weight: 700;
            }
            .campaign-product-state { font-size: 11px; }

            /* Keep Color and Option buttons horizontal on mobile as on desktop. */
            .campaign-color-selector .btn-group-toggle,
            .campaign-size-selector .btn-group-toggle,
            .campaign-option-selector .btn-group-toggle {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                gap: 3px;
            }
            .campaign-color-selector .btn,
            .campaign-size-selector .btn,
            .campaign-option-selector .btn {
                margin: 0 !important;
                padding: .2rem .3rem;
                font-size: 9.5px;
                line-height: 1.25;
                white-space: nowrap;
            }

            .campaign-order-table {
                table-layout: fixed;
                width: 100%;
                font-size: 10.5px;
            }
            .campaign-order-table th,
            .campaign-order-table td {
                padding: .35rem .15rem;
                vertical-align: top;
                word-break: break-word;
            }
            .campaign-order-table .product-image { width: 44px; }
            .campaign-order-table td.product-image {
                display: flex !important;
                flex-direction: column !important;
                align-items: center;
                gap: 3px;
            }
            .campaign-order-table .product-image img {
                max-width: 36px !important;
                height: auto;
            }
            .campaign-order-table .product-image .btn {
                padding: .15rem .3rem;
                line-height: 1.1;
            }
            .campaign-order-table .product-name { width: 100px; }
            .campaign-order-table .product-price { width: 40px; }
            .campaign-order-table .product-quantity {
                width: 38px;
                text-align: center;
            }
            .campaign-order-table .product-total { width: 44px; }

            /* Mobile quantity control: +, quantity and - are stacked vertically. */
            .campaign-order-table .input-spinner {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                width: 34px;
                min-width: 34px;
                max-width: 34px;
                margin: 0 auto;
                flex-wrap: nowrap;
            }
            .campaign-order-table .input-spinner .input-group-prepend,
            .campaign-order-table .input-spinner .input-group-append {
                display: block;
                width: 100%;
                margin: 0;
            }
            .campaign-order-table .input-spinner .btn {
                display: block;
                width: 100%;
                min-width: 34px;
                padding: .12rem .2rem;
                line-height: 1.2;
                border-radius: 0;
            }
            .campaign-order-table .input-spinner .form-control {
                display: block;
                width: 100%;
                min-width: 34px;
                max-width: 34px;
                height: 28px;
                flex: 0 0 28px;
                padding: .1rem;
                text-align: center;
                border-radius: 0;
            }
        }
    </style>
    @stack('css')
    {!! Settings::get('facebook_pixels') !!}

    @php
        /*
         * Campaign funnel tracking uses the same event names/shape already
         * used by the normal product -> checkout -> thank-you flow.
         * The campaign page is both a product-detail and checkout surface, so
         * view_item and begin_checkout are emitted here. Purchase continues to
         * fire from the existing thankyou.blade.php after a successful order.
         */
        $campaignTrackingItems = [];
        $campaignCheckoutProducts = [];

        foreach (Cart::content() as $trackingItem) {
            $trackingProduct = $trackingItem->model;
            $trackingCategory = 'Uncategorized';

            if ($trackingProduct && $trackingProduct->category) {
                $trackingCategory = $trackingProduct->category->name;
            }

            $trackingVariant = trim(implode(' ', array_filter([
                $trackingItem->options->colorName ?? null,
                $trackingItem->options->sizeName ?? null,
                $trackingItem->options->optionName ?? null,
            ])));

            $campaignTrackingItems[] = [
                'item_name' => $trackingProduct ? $trackingProduct->productName : $trackingItem->name,
                'item_id' => (string) $trackingItem->id,
                'price' => (float) $trackingItem->price,
                'item_category' => $trackingCategory,
                'item_variant' => $trackingVariant,
                'quantity' => (int) $trackingItem->qty,
                'currency' => 'BDT',
            ];

            $campaignCheckoutProducts[] = [
                'id' => (string) $trackingItem->id,
                'name' => $trackingProduct ? $trackingProduct->productName : $trackingItem->name,
                'price' => (float) $trackingItem->price,
                'quantity' => (int) $trackingItem->qty,
                'category' => $trackingCategory,
                'variant' => $trackingVariant,
            ];
        }

        $campaignTrackingValue = (float) str_replace(',', '', Cart::subtotal('0', '', ''));
    @endphp

    <script>
        window.dataLayer = window.dataLayer || [];

        var campaignTrackingItems = {!! json_encode($campaignTrackingItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
        var campaignCheckoutProducts = {!! json_encode($campaignCheckoutProducts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
        var campaignTrackingValue = {{ $campaignTrackingValue }};
        var campaignName = {!! json_encode($campaign_data->name ?: $campaign_data->banner_title, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
        var campaignSlug = {!! json_encode($campaign_data->slug, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

        // Campaign-specific page event.
        window.dataLayer.push({
            event: 'view_campaign',
            pageType: 'campaign',
            campaignId: {{ (int) $campaign_data->id }},
            campaignName: campaignName,
            campaignSlug: campaignSlug,
            ecommerce: {
                currency: 'BDT',
                value: campaignTrackingValue
            }
        });

        // Same ecommerce event used on the normal product details page.
        if (campaignTrackingItems.length > 0) {
            window.dataLayer.push({
                event: 'view_item',
                value: campaignTrackingValue,
                ecommerce: {
                    items: campaignTrackingItems
                }
            });
        }

        // Campaign page contains the checkout/order form, so mirror the normal
        // checkout page's begin_checkout event shape for existing GTM triggers.
        window.dataLayer.push({
            event: 'begin_checkout',
            value: campaignTrackingValue,
            currency: 'BDT',
            ecommerce: {
                checkout: {
                    actionField: {
                        step: 1,
                        option: 'Campaign Checkout'
                    },
                    products: campaignCheckoutProducts
                }
            },
            campaign: {
                id: {{ (int) $campaign_data->id }},
                name: campaignName,
                slug: campaignSlug
            }
        });

        // Mirror the standard Meta funnel used by product/checkout pages.
        if (typeof fbq === 'function' && campaignTrackingItems.length > 0) {
            var primaryCampaignItem = campaignTrackingItems[0];

            fbq('track', 'ViewContent', {
                content_ids: [String(primaryCampaignItem.item_id)],
                content_name: primaryCampaignItem.item_name,
                content_category: primaryCampaignItem.item_category || 'Uncategorized',
                value: Number(primaryCampaignItem.price || 0),
                currency: 'BDT'
            });

            fbq('track', 'InitiateCheckout', {
                content_ids: campaignTrackingItems.map(function (item) {
                    return String(item.item_id);
                }),
                content_name: campaignName,
                content_category: 'Campaign',
                num_items: campaignTrackingItems.reduce(function (total, item) {
                    return total + Number(item.quantity || 0);
                }, 0),
                value: campaignTrackingValue,
                currency: 'BDT'
            });
        }
    </script>
    
    <style>
        
        /* Style for selected product card */
        .selected {
            border: 2px solid green; /* Change border color to green */
        }
        .countdown-container {
            text-align: center;
        }
        .counter-card {
            border: 2px dotted white; /* Dotted border */
            border-radius: 15px; /* Rounded corners */
            padding: 5px; /* Padding for the card */
            background-color: transparent; /* Slightly transparent white background */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            text-align: center; /* Center the text within each card */
           
        }
        .counter-card div{
            font-size: 1.2em;
            font-weight:bolder;
            color:white;
        }
        
        
        .counter-card span {
            display: block; /* Make the span block-level for better spacing */
            font-size: 0.8em; /* Font size for labels */
            color:orange;
        }
    
        
       
        .form_inn{
            padding:10px;
        }
        @media (max-width: 992px) {
            .campro_inn,.cont_inner,.cont_num ,.discount_inn{
                padding: 10px!important; /* Add 10px padding for tablet and smaller devices */
                width: 100%;
            }
            .discount_inn{
                margin:10px 0 0 0;
            }
            .campro_inn h2{
                font-size:20px;
            }
        }

    </style>
    <style>
        .button-3d {
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
    
       
        
    
        .button-3d:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
    
       
    
    </style>
    <style>
        .button-animated-border {
            position: relative;
            overflow: hidden;
            border: 3px solid white; /* Initial border */
            border-radius: 10px; /* Optional: for rounded corners */
            transition: color 0.3s ease; /* Transition for text color */
            animation: border-animation 3s linear infinite; /* Animation */
        }
    
        
    
        @keyframes border-animation {
            0% {
                border-color: white; /* Transparent at start */
                transform: scale(0.95); /* Initial scale */
            }
            25% {
                border-color: yellow; /* Fill with white */
                transform: scale(1); /* Slightly grow */
            }
            50% {
                border-color: white; /* Transparent in middle */
                transform: scale(0.95); /* Back to original scale */
            }
            75% {
                border-color: yellow; /* Fill with white again */
                transform: scale(1); /* Slightly grow again */
            }
            100% {
                border-color: white; /* Transparent at end */
                transform: scale(0.95); /* Back to original scale */
            }
        }
    
        .button-animated-border:hover {
            color: #fff; /* Change text color on hover */
        }
    </style>
    <style>
        .price-regular {
            font-size: 24px;
            color: #f44336;
            position: relative;
            display: inline-block;
            font-weight: bold;
        }
    
        .price-regular::before,
        .price-regular::after {
            content: "";
            position: absolute;
            height: 2px;
            background: red;
            width: 100%;
            top: 40%;
            left: 0;
            transform: rotate(-10deg);
        }
    
        .price-regular::after {
            top: 60%;
            transform: rotate(10deg);
        }
    
        .price-sale {
            font-size: 28px;
            font-weight: bold;
            color: #FDDF31;
            animation: pulse 1.2s infinite;
        }
    
        @keyframes pulse {
            0% {
                text-shadow: 0 0 0px #00e676;
            }
            50% {
                text-shadow: 0 0 8px #00e676;
            }
            100% {
                text-shadow: 0 0 0px #00e676;
            }
        }
        .price-sale-animated {
            margin-left:20px;
            font-size: 28px;
            font-weight: bold;
            color: #FDDF31;
            background-color: #1b1b1b;
            padding: 8px 16px;
            border-radius: 50px;
            border: 2px solid #FDDF31;
            position: relative;
            animation: glow-border 1.5s infinite alternate;
            display: inline-block;
        }
    
        @keyframes glow-border {
            0% {
                box-shadow: 0 0 5px #00e676, 0 0 10px #00e676;
            }
            100% {
                box-shadow: 0 0 15px #00e676, 0 0 25px #00e676;
            }
        }
        
    </style>
    <style>
    @keyframes pulse {
        0% {
            transform: scale(1);
            box-shadow: 0 0 10px rgba(233, 30, 99, 0.6);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(233, 30, 99, 1);
        }
        100% {
            transform: scale(1);
            box-shadow: 0 0 10px rgba(233, 30, 99, 0.6);
        }
    }
    </style>
    <!-- LEATHERKET shared Palatino typography: after vendor/theme styles. -->
    <link href="{{ asset('assets/css/leatherket-typography.css') }}?v=1" rel="stylesheet" type="text/css" />
</head>

<body>
<section style="background-image: radial-gradient(at center center, #171515 28%, #171515 79%)">
    <div class="container py-3 py-md-5">
        
        <div class="row gy-2 justify-content-between align-items-center">
            <div class="col-md-6 text-start">
                <h2 class=" py-2 py-md-4 fw-bolder" style="color:#FDDF31">{!! $campaign_data->banner_title  !!} </h2>
                <h5 class="text-light fs-4 font-weight-normal">{!! $campaign_data->short_description !!}</h5>
                <h4 class="text-light mt-3 text-center">
                    রেগুলার মূল্য <span class="price-regular"> ৳ {{$product->productRegularPrice}} টাকা </span>
                </h4>
                <h4 class="text-light mt-3 text-center">
                    অফার মূল্য <span class="price-sale price-sale-animated"> ৳ {{$product->productSalePrice}} টাকা </span>
                </h4>
                <div style="text-align: center; margin: 40px 0;">
                    <a href="#order_form" style="
                        display: inline-block;
                        font-size: 24px;
                        font-weight: bold;
                        color: white;
                        background: linear-gradient(to right, #FF5722, #E91E63);
                        padding: 16px 40px;
                        border: none;
                        border-radius: 50px;
                        text-decoration: none;
                        box-shadow: 0 0 10px rgba(233, 30, 99, 0.6);
                        animation: pulse 1.5s infinite;
                        transition: transform 0.3s ease;
                    ">
                         অর্ডার করতে ক্লিক করুন 
                    </a>
                </div>

            </div>
             <div class="col-md-6">
                <img class="img-fluid shadow border rounded" src="{{\App\Support\PublicMedia::url('campaigns/'.$campaign_data->banner)}}" >
            </div>
        </div>
    </div>
</section>
   
@if($campaign_data->video!=null)
<section class="camp_video_sec">
    <div class="container">
    
        <div class="row justify-content-center gy-2 gy-md-4">
            <div class="col-md-8">
                <h2 class="p-2 py-md-3 rounded text-center" style="background-color:black;border:green 2px solid;color:white;font-weight:bolder">প্রডাক্টের "ভিডিও দেখুন"</h2>
            </div>
            <div class="col-md-8 col-sm-12">
                <div class="camp_vid rounded" style="border:5px solid red">
                    <iframe width="100%" height="480" 
                    src="https://www.youtube.com/embed/{{$campaign_data->video}}" 
                    title="{{$campaign_data->banner_title}}" Recipe"" frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen=""></iframe>
                </div>
            </div>
            <div class="col-sm-12">
                <div style="text-align: center; margin: 40px 0;">
                    <a href="#order_form" style="
                        display: inline-block;
                        font-size: 24px;
                        font-weight: bold;
                        color: white;
                        background: linear-gradient(to right, #FF5722, #E91E63);
                        padding: 16px 40px;
                        border: none;
                        border-radius: 50px;
                        text-decoration: none;
                        box-shadow: 0 0 10px rgba(233, 30, 99, 0.6);
                        animation: pulse 1.5s infinite;
                        transition: transform 0.3s ease;
                    ">
                         অর্ডার করতে ক্লিক করুন 
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif
<section class="py-2 py-md-4" style="background: linear-gradient(to bottom, #FAF4B3, #ECC7CF);">
    <div class="container my-2 my-md-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @php
                    $campaignPhone = trim((string) $campaign_data->phone_number);
                    $campaignWhatsapp = trim((string) $campaign_data->whatsapp_number);
                    $campaignWhatsappLink = preg_replace('/\D+/', '', $campaignWhatsapp);
                    if ($campaignWhatsappLink && substr($campaignWhatsappLink, 0, 1) === '0') {
                        $campaignWhatsappLink = '88' . $campaignWhatsappLink;
                    }
                @endphp

                <h2 class="text-center p-2 p-md-4 rounded" style="background-color:#FBEFF7; border:2px dashed #F1ACE7;">
                    @if($campaignPhone)
                        আমাদের থেকে বিস্তারিত জানতে এই
                        <a href="tel:{{ $campaignPhone }}" style="color:#d63384; font-weight: bold; text-decoration: underline;">
                            {{ $campaignPhone }}
                        </a>
                        নাম্বারে কল করুন
                    @else
                        আমাদের থেকে বিস্তারিত জানতে যোগাযোগ করুন
                    @endif
                </h2>

                <div class="row justify-content-center my-2 my-md-4 gy-2">
                    @if($campaignPhone)
                    <div class="col-md-6 custom_btn">
                        <div class="shadow-lg">
                            <a href="tel:{{ $campaignPhone }}"
                               class="btn btn-danger btn-lg d-block py-md-3 fs-2 fw-bolder button-3d button-animated-border">
                                আমাদের কল করুন <i class="fas fa-phone"></i>
                            </a>
                        </div>
                    </div>
                    @endif

                    @if($campaignWhatsappLink)
                    <div class="col-md-6">
                        <div class="shadow-lg">
                            <a href="https://wa.me/{{ $campaignWhatsappLink }}"
                               target="_blank" rel="noopener"
                               class="btn btn-success btn-lg d-block py-md-3 fs-2 text-light fw-bolder button-3d button-animated-border">
                                <i class="fab fa-whatsapp"></i> হোয়াটসঅ্যাপ
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
                <div style="text-align: center; margin: 40px 0;">
                    <a href="#order_form" style="
                        display: inline-block;
                        font-size: 24px;
                        font-weight: bold;
                        color: white;
                        background: linear-gradient(to right, #FF5722, #E91E63);
                        padding: 16px 40px;
                        border: none;
                        border-radius: 50px;
                        text-decoration: none;
                        box-shadow: 0 0 10px rgba(233, 30, 99, 0.6);
                        animation: pulse 1.5s infinite;
                        transition: transform 0.3s ease;
                    ">
                         অর্ডার করতে ক্লিক করুন 
                    </a>
                </div>
                
                
            
            </div>
        </div>
    </div>
</section>
@if(optional($campaign_data)->description && strlen($campaign_data->description) > 15)
{!!$campaign_data->description !!} 
@endif
<section class="my-3 my-md-4">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="campro_inn">
                    <div class="campro_head">
                        <h2 class="text-center mb-3">{{$product->productName}}</h2>
                    </div>

                    <div class="campro_img_slider owl-carousel">
                        @if($campaign_data->image_one)
                       <div class="campro_img_item" style="border:5px solid red;" class="rounded">
                           <img  class="rounded" src="{{\App\Support\PublicMedia::url('campaigns/'.$campaign_data->image_one)}}" alt="">
                       </div> 
                       @endif
                        @if($campaign_data->image_two)
                       <div class="campro_img_item" style="border:5px solid red;" class="rounded">
                           <img  class="rounded" src="{{\App\Support\PublicMedia::url('campaigns/'.$campaign_data->image_two)}}" alt="">
                       </div> 
                       @endif
                        @if($campaign_data->image_three)
                       <div class="campro_img_item" style="border:5px solid red;" class="rounded">
                           <img  src="{{\App\Support\PublicMedia::url('campaigns/'.$campaign_data->image_three)}}" alt="">
                       </div>
                       @endif
                    </div>
                    <div style="text-align: center; margin: 40px 0;">
                        <a href="#order_form" style="
                            display: inline-block;
                            font-size: 24px;
                            font-weight: bold;
                            color: white;
                            background: linear-gradient(to right, #FF5722, #E91E63);
                            padding: 16px 40px;
                            border: none;
                            border-radius: 50px;
                            text-decoration: none;
                            box-shadow: 0 0 10px rgba(233, 30, 99, 0.6);
                            animation: pulse 1.5s infinite;
                            transition: transform 0.3s ease;
                        ">
                             অর্ডার করতে ক্লিক করুন 
                        </a>
                    </div>
                 
                </div>

            </div>
        </div>
    </div>
</section>
<section class="section-content padding-y bg slidetop" id="order_form" style="background: linear-gradient(to bottom, #FFEC84, #FFFFFF);">
    <div class="container">
        <h2 class="campaign_offer text-center mb-4">অফারটি সীমিত সময়ের জন্য, তাই অফার শেষ হওয়ার আগেই অর্ডার করুন</h2>
        <div class="row">
            <div class="col-md-6 order-2 order-md-1">
                <aside class="card mb-4">
                    <article class="card-body">
                        <header class="mb-4">
                            <p class="text-center" style="font-size: 16px;">অর্ডারটি কনফার্ম করতে আপনার নাম, ঠিকানা,
                                মোবাইল নাম্বার, লিখে <span class="text-danger">অর্ডার কনফার্ম করুন</span> বাটনে
                                ক্লিক করুন
                            </p>
                        </header>
                        <div class="row">
                                <div class="form-group col-sm-12">
                                    <label>আপনার নাম </label>
                                    <input onchange="saveInput()" type="text" id="customerName" placeholder="আপনার নাম লিখুন" class="form-control">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label>আপনার মোবাইল নম্বর </label>
                                    <input onchange="saveInput()" type="number" pattern="[0-9]*" id="customerPhone" placeholder="আপনার মোবাইল নম্বর লিখুন"
                                           class="form-control">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label>আপনার সম্পূর্ণ ঠিকানা </label>
                                    <input onchange="saveInput()" type="text"  id="customerAddress" class="form-control"
                                           placeholder="আপনার ঠিকানা সম্পূর্ণ  লিখুন">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label class="form-label d-block">Select Area <span class="text-danger">*</span></label>
                                    @php
                                        $shipingCharges = App\ShippingCharge::all();
                                    @endphp
                                    @if ($shipingCharges->isNotEmpty())
                                        <div class="shipping-options">
                                            @foreach ($shipingCharges as $index => $shipingCharge)
                                                <label class="shipping-option {{ $index === 0 ? 'active' : '' }}">
                                                    <input
                                                        type="radio"
                                                        name="area"
                                                        id="area-{{ $shipingCharge->id }}"
                                                        value="{{ $shipingCharge->charge }}"
                                                        {{ $index === 0 ? 'checked' : '' }}
                                                        required
                                                    >
                                                    {{ $shipingCharge->name }}
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <p>No shipping charges available.</p>
                                    @endif
                                </div>
                                {{--
                                <div class="form-group col-sm-12">
                                    <label class="form-label d-block">Select Area <span class="text-danger">*</span></label>
                                    @php
                                        $shipingCharges = App\ShippingCharge::all();
                                    @endphp
                                    @if ($shipingCharges->isNotEmpty())
                                        <div class="btn-group-toggle" data-toggle="buttons">
                                            @foreach ($shipingCharges as $index => $shipingCharge)
                                                <label class="btn btn-outline-primary {{ $index === 0 ? 'active' : '' }}">
                                                    <input 
                                                        type="radio" 
                                                        name="area" 
                                                        id="area-{{ $shipingCharge->id }}" 
                                                        value="{{ $shipingCharge->charge }}" 
                                                        autocomplete="off" 
                                                        {{ $index === 0 ? 'checked' : '' }}
                                                        required
                                                    >
                                                    {{ $shipingCharge->name }}
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <p>No shipping charges available.</p>
                                    @endif
                                </div>
                                --}}
                            </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="button" id="orderConfirm"
                                        class="btn btn-lg btn-info btn-base-1 btn-block btn-icon-left strong-500 hov-bounce hov-shaddow buy-now"
                                        style="font-size:20px !important;"> অর্ডার কনফার্ম করুন </button>
                            </div>
                            
                        </div>
                    </article> <!-- card-body.// -->
                </aside>
            </div>
            <div class="col-md-6 order-1 order-md-2 mb-4 mb-md-0">
                <div class="campaign-product-picker">
                    <div class="campaign-product-picker-title">Select Product</div>
                    <div class="campaign-product-options">
                        @foreach($campaignProducts->values() as $campaignProductIndex => $campaignProductItem)
                            @php
                                $campaignProductPrice = $campaignProductItem->price();
                                $campaignFirstOption = $campaignProductItem->options->first();
                                if ($campaignFirstOption && $campaignFirstOption->pivot->price !== null && is_numeric($campaignFirstOption->pivot->price)) {
                                    $campaignProductPrice = (float) $campaignFirstOption->pivot->price;
                                }
                                $campaignProductSelected = $campaignProductIndex === 0;
                            @endphp
                            <label class="campaign-product-choice {{ $campaignProductSelected ? 'active' : '' }}" data-product-id="{{ $campaignProductItem->id }}">
                                <input
                                    type="checkbox"
                                    class="campaign-product-checkbox"
                                    value="{{ $campaignProductItem->id }}"
                                    {{ $campaignProductSelected ? 'checked' : '' }}
                                    onchange="toggleCampaignProduct(this)"
                                >
                                <span class="campaign-product-check"><i class="fas fa-check"></i></span>
                                <span class="campaign-product-meta">
                                    <span class="campaign-product-name">{{ $campaignProductItem->productName }}</span>
                                    <span class="campaign-product-price">TK {{ number_format((float) $campaignProductPrice, 0, '.', '') }}</span>
                                </span>
                                <span class="campaign-product-state">{{ $campaignProductSelected ? 'Selected' : 'Select' }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="orderDetails">
                    @include('website.partials.campaign_order_details')
                </div>
            </div>

        </div>
    </div>
</section>
<script src="{{asset('assets/js/lazyload.js')}}"></script>
<script src="{{ asset('assets/js/sweetalert2.min.js') }}"></script>
<script src="{{asset('assets/js/script.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script>
    $('.campro_img_slider').owlCarousel({   
        dots: false,
        arrow: false,
        autoplay: true,
        loop: true,
        margin: 10,
        smartSpeed: 1000,
        mouseDrag: true,
        touchDrag: true,
        items: 3,
        responsiveClass: true,
        responsive: {
            300: {
                items: 1,
            },
            480: {
                items: 2,
            },
            768: {
                items: 3,
            },
            1170: {
                items: 3,
            },
        }
    });
</script>
<script>
    $(document).ready(function() {
        updateNavCart();
        
    });
    function showFrontendAlert(type, message) {
        if (type === 'danger') {
            type = 'error';
        }
        swal({
            position: 'top-end',
            type: type,
            title: message,
            showConfirmButton: false,
            timer: 3000
        });
    }
    function updateNavCart() {
        $.ajax({
            type: "get",
            url: "{{url('/miniCart')}}",
            contentType: "application/json",
            success: function (response) {
                $('#cart_items').empty().prepend(response);
            }
        });
    }

    function removeFromCart(key) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            type: "DELETE",
            url: "{{url('/checkout')}}/" + key,
            data: {
                '_token': '{{ csrf_token() }}'
            },
            contentType: "application/json",
            success: function (response) {

                showFrontendAlert('success', 'Successfully Product Removed from Cart');
                updateNavCart();
                // Campaign order rows remove via the product selector; refresh if an old remove button calls this.
                location.reload();

            }
        });
    }

    function getCampaignCurrentTrackingState() {
        var items = [];
        var checkoutProducts = [];
        var value = 0;

        $('.orderDetails .campaign-tracking-item').each(function () {
            var $row = $(this);
            var price = Number($row.attr('data-price') || 0);
            var quantity = Number($row.attr('data-quantity') || 0);
            var variantParts = [
                $row.attr('data-color') || '',
                $row.attr('data-size') || '',
                $row.attr('data-option') || ''
            ].filter(function (part) {
                return String(part).trim() !== '';
            });
            var variant = variantParts.join(' ');
            var itemId = String($row.attr('data-product-id') || '');
            var itemName = String($row.attr('data-product-name') || '');
            var category = String($row.attr('data-category') || 'Uncategorized');

            if (!itemId || quantity <= 0) {
                return;
            }

            items.push({
                item_name: itemName,
                item_id: itemId,
                price: price,
                item_category: category,
                item_variant: variant,
                quantity: quantity,
                currency: 'BDT'
            });

            checkoutProducts.push({
                id: itemId,
                name: itemName,
                price: price,
                quantity: quantity,
                category: category,
                variant: variant
            });

            value += price * quantity;
        });

        return {
            items: items,
            checkoutProducts: checkoutProducts,
            value: Number(value.toFixed(2))
        };
    }

    function pushCampaignCheckoutTracking(updateSource) {
        var state = getCampaignCurrentTrackingState();

        if (!state.items.length) {
            return;
        }

        window.dataLayer = window.dataLayer || [];

        // For user-initiated cart changes, record the cart update first.
        // The refreshed begin_checkout is pushed immediately after it so the
        // DataLayer order is: campaign_cart_update -> begin_checkout.
        window.dataLayer.push({
            event: 'campaign_cart_update',
            value: state.value,
            currency: 'BDT',
            items: state.items,
            campaign_id: {{ (int) $campaign_data->id }},
            campaign_slug: campaignSlug,
            update_source: updateSource || 'campaign_cart_update'
        });

        // Push the complete refreshed checkout state after the cart-update event.
        // Do not push { ecommerce: null } here because DataLayer Checker displays
        // that reset as a separate generic `data` event.
        window.dataLayer.push({
            event: 'begin_checkout',
            value: state.value,
            currency: 'BDT',
            ecommerce: {
                checkout: {
                    actionField: {
                        step: 1,
                        option: 'Campaign Checkout'
                    },
                    products: state.checkoutProducts
                }
            },
            campaign: {
                id: {{ (int) $campaign_data->id }},
                name: campaignName,
                slug: campaignSlug
            },
            update_source: updateSource || 'campaign_cart_update'
        });
    }

    function refreshCampaignOrderDetails(html, updateSource, shouldTrack) {
        $('.orderDetails').html(html);
        updateNavCart();

        // The first delivery-charge sync happens automatically on page load.
        // The page-level begin_checkout event has already been pushed above,
        // so skip tracking only for that initial refresh to avoid a duplicate.
        if (shouldTrack !== false) {
            pushCampaignCheckoutTracking(updateSource);
        }
    }

    function setCampaignProductChoiceState($choice, selected) {
        $choice.toggleClass('active', selected);
        $choice.find('.campaign-product-state').text(selected ? 'Selected' : 'Select');
    }

    function toggleCampaignProduct(element) {
        var $checkbox = $(element);
        var $choice = $checkbox.closest('.campaign-product-choice');
        var productId = $checkbox.val();
        var selected = $checkbox.is(':checked');

        $checkbox.prop('disabled', true);

        $.post("{{ route('campaign.cart.product') }}", {
            _token: '{{ csrf_token() }}',
            campaign_id: {{ (int) $campaign_data->id }},
            product_id: productId,
            selected: selected ? 1 : 0
        }, function(response) {
            if (response.status !== 'success') {
                $checkbox.prop('checked', !selected);
                setCampaignProductChoiceState($choice, !selected);
                showFrontendAlert('error', response.message || 'Could not update product selection.');
                return;
            }

            setCampaignProductChoiceState($choice, selected);
            refreshCampaignOrderDetails(response.html, 'product_selection');
        }).fail(function(xhr) {
            $checkbox.prop('checked', !selected);
            setCampaignProductChoiceState($choice, !selected);

            var message = 'Could not update product selection. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showFrontendAlert('error', message);
        }).always(function() {
            $checkbox.prop('disabled', false);
        });
    }

    function removeCampaignProduct(productId) {
        var $checkbox = $('.campaign-product-checkbox[value="' + productId + '"]');
        if (!$checkbox.length) {
            return;
        }

        $checkbox.prop('checked', false);
        toggleCampaignProduct($checkbox.get(0));
    }

    function updateQuantity(key, element, shouldTrack) {
        var quantity = (element && typeof element === 'object' && typeof element.value !== 'undefined') ? element.value : 0;
        var trackThisUpdate = (typeof shouldTrack === 'undefined') ? true : shouldTrack;

        $.post("{{ route('campaign.cart.quantity') }}", {
            _token: '{{ csrf_token() }}',
            key: key,
            quantity: quantity
        }, function(data){
            refreshCampaignOrderDetails(data, 'quantity_change', trackThisUpdate);
        }).fail(function(xhr) {
            showFrontendAlert('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Stock quantity is unavailable.');
            $.post("{{ route('campaign.cart.quantity') }}", { _token: '{{ csrf_token() }}', key: '', quantity: 0 }, function(html) {
                refreshCampaignOrderDetails(html, 'quantity_change', false);
            });
        });
    }

    function updateCampaignOption(key, optionId) {
        $.get("{{ route('campaign.cart.option') }}", {
            _token: '{{ csrf_token() }}',
            key: key,
            option_id: optionId
        }, function(data) {
            refreshCampaignOrderDetails(data, 'option_change');
        }).fail(function(xhr) {
            var message = 'Could not update product option. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showFrontendAlert('error', message);
        });
    }

    function updateCampaignColor(key, colorId) {
        $.post("{{ route('campaign.cart.color') }}", {
            _token: '{{ csrf_token() }}',
            key: key,
            color_id: colorId
        }, function(data) {
            refreshCampaignOrderDetails(data, 'color_change');
        }).fail(function(xhr) {
            var message = 'Could not update product color. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showFrontendAlert('error', message);
        });
    }

    function updateCartOptions(key, value, optionType) {
      
        $.get("{{ route('updateCartOptions') }}", {
            _token: '{{ csrf_token() }}', // Include CSRF token
            key: key,
            [optionType] : value
        }, function(data) {
            refreshCampaignOrderDetails(data, 'cart_option_change');
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('Error updating cart options:', textStatus, errorThrown);
            alert('Could not update cart options. Please try again.');
        });
    }

    function addToCart(id) {
        var colorId = $('input[name="color"]:checked').val();
        var sizeId = $('input[name="size"]:checked').val();
        var quantity = $('input[name="quantity"]').val() || 1;
        $.post("{{url('/checkout')}}", {
            _token: '{{ csrf_token() }}',
            id: id,
            quantity: quantity,
            color_id: colorId,
            size_id: sizeId
        }, function (data) {
            console.log(data.message);
            showFrontendAlert(data.status, data.message);
            updateNavCart();
          
        });
    }

    function buyNow(id) {
        var colorId = $('input[name="color"]:checked').val();
        var sizeId = $('input[name="size"]:checked').val();
        var quantity = $('input[name="quantity"]').val() || 1;
    
       // Check if color and size are selected
        if ($('#color-select').length && !colorId) {
            showFrontendAlert('error', 'Please select a color.');
            return;
        }

        if ($('#size-select').length && !sizeId) {
            showFrontendAlert('error', 'Please select a size.');
            return;
        }
        $.post("{{url('/checkout')}}", {
            _token: '{{ csrf_token() }}',
            id: id,
            quantity: quantity,
            color_id: colorId,
            size_id: sizeId
        }, function (data) {
            showFrontendAlert(data.status, data.message);
            updateNavCart();
            if(data.status == 'success'){
                window.location.href = '{{url('/checkout')}}';
            }
            
        });
    }


    function cartQuantityInitialize() {
        $('.btn-number').click(function (e) {
            e.preventDefault();

            fieldName = $(this).attr('data-field');
            type = $(this).attr('data-type');
            var input = $("input[name='" + fieldName + "']");
            var currentVal = parseInt(input.val());

            if (!isNaN(currentVal)) {
                if (type == 'minus') {

                    if (currentVal > input.attr('min')) {
                        input.val(currentVal - 1).change();
                    }
                    if (parseInt(input.val()) == input.attr('min')) {
                        $(this).attr('disabled', true);
                    }

                } else if (type == 'plus') {

                    if (currentVal < input.attr('max')) {
                        input.val(currentVal + 1).change();
                    }
                    if (parseInt(input.val()) == input.attr('max')) {
                        $(this).attr('disabled', true);
                    }

                }
            } else {
                input.val(0);
            }
        });

        $('.input-number').focusin(function () {
            $(this).data('oldValue', $(this).val());
        });

        $('.input-number').change(function () {

            minValue = parseInt($(this).attr('min'));
            maxValue = parseInt($(this).attr('max'));
            valueCurrent = parseInt($(this).val());

            name = $(this).attr('name');
            if (valueCurrent >= minValue) {
                $(".btn-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled')
            } else {
                alert('Sorry, the minimum value was reached');
                $(this).val($(this).data('oldValue'));
            }
            if (valueCurrent <= maxValue) {
                $(".btn-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled')
            } else {
                alert('Sorry, the maximum value was reached');
                $(this).val($(this).data('oldValue'));
            }


        });
        $(".input-number").keydown(function (e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                // let it happen, don't do anything
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    }


</script>
<script>
        $(document).ready(function () {
            // The checked delivery area is programmatically triggered once on load.
            // Suppress only that first AJAX refresh from emitting a second
            // begin_checkout; later user-initiated area changes still track normally.
            var isInitialDeliverySync = true;

            $('input[name="area"]').on('change', function () {
                var selectedCourier = $('input[name="area"]:checked');
                var selectCourier = selectedCourier.val();
                var shouldTrackDeliveryChange = !isInitialDeliverySync;
                isInitialDeliverySync = false;

                $('.shipping-option').removeClass('active');
                $(this).closest('.shipping-option').addClass('active');

                saveInput();

                $.ajax({
                    type: "get",
                    url: "{{url('/updateDeliveryCharge')}}",
                    data: {
                        'selectCourier': selectCourier,
                        '_token': '{{ csrf_token() }}'
                    },
                    success: function () {
                        updateQuantity('', 0, shouldTrackDeliveryChange);
                    }
                });
            });

            $('input[name="area"]:checked').trigger('change');

            $(document).on("click", "#orderConfirm", function () {
                constantValue = 0;
                var bdPhoneRegex = /^(013|014|015|016|017|018|019)\d{8}$/;
                var customerName = $('#customerName');
                var customerAddress = $('#customerAddress');
                var customerPhone = $('#customerPhone');
                var selectCourier = $('input[name="area"]:checked');
                
                // Log all values
                
                
                
                if (!customerName.val()) {
                    customerName.addClass("has-error");
                    constantValue = 1;
                    showFrontendAlert('error', 'Invalied Customenr Name!');
                }
                if (!customerAddress.val()) {
                    customerAddress.addClass("has-error");
                    constantValue = 1;
                    showFrontendAlert('error', 'Invalied Customer Address!');
                }
                if (!customerPhone.val()) {
                    customerPhone.addClass("has-error");
                    constantValue = 1;
                    showFrontendAlert('error', 'Invalied Phone Number!');
                }
                if (!bdPhoneRegex.test(customerPhone.val())) {
                    customerPhone.addClass("has-error");
                    constantValue = 1;
                    showFrontendAlert('error', 'Invalied Phone Number!');
                }
                
                if (!selectCourier.length || !selectCourier.val()) {
                    $('input[name="area"]').closest('.form-group').addClass("has-error");
                    showFrontendAlert('error', 'Please Select Area');
                    constantValue = 1;
                }
                // console.log(selectCourier); // Log the selected charge value

                // if (!selectCourier) {
                    
                //     // If no radio button is selected, handle the error
                //     $('input[name="area"]').closest('.form-group').addClass("has-error");
                //     showFrontendAlert('error', 'Please Aelect Area');
                //     constantValue = 1;
                // }
                console.log("Customer Name:", customerName.val());
                console.log("Customer Address:", customerAddress.val());
                console.log("Customer Phone:", customerPhone.val());
                console.log("Is Phone Valid:", bdPhoneRegex.test(customerPhone.val()));
                console.log("Selected Courier:", selectCourier.val(), "-", selectCourier.text());
                if (constantValue === 1) {
                    $('html, body').animate(  {  scrollTop: $('body').position().top  },  500   );
                } else {
                    // Push the final selected products/price/quantity state immediately
                    // before the order request so GTM/Meta receives the exact checkout cart.
                    pushCampaignCheckoutTracking('order_confirm');

                    $.ajax({
                        type: "post",
                        url: "{{url('/placeOrder')}}",
                        data: {
                            'customerName': customerName.val(),
                            'customerAddress': customerAddress.val(),
                            'customerPhone': customerPhone.val(),
                            'selectCourier': selectCourier.val(),
                            '_token': '{{ csrf_token() }}'
                        },
                        success: function (data) {
                            if(data['status'] === 'success'){
                                showFrontendAlert('success', 'Successfully Place order');
                                window.location.href = data['link'];

                            }else if(data['status'] === 'blocked'){
                                showFrontendAlert('error', data['message']);
                            }
                            else{
                                showFrontendAlert('error', 'Unsuccessful to Place order');

                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX Error:', error);
                            console.log('Status:', status);
                            console.log('Response:', xhr.responseText);
                            showFrontendAlert('error', 'Something went wrong while placing the order. Please try again.');
                        }
                    });
                }
            });
        });

        
    </script>
    <script>

function saveInput() {
    let customerPhone = document.querySelector('#customerPhone')?.value;
    let customerName = document.querySelector('#customerName')?.value;
    let customerAddress = document.querySelector('#customerAddress')?.value;
    let selectedArea = document.querySelector('input[name="area"]:checked');
    let selectCourier = selectedArea ? selectedArea.value : '';
    let csrfToken = '{{ csrf_token() }}';
    
    console.log("Customer Phone:", customerPhone);
    console.log("Customer Name:", customerName);
    console.log("Customer Address:", customerAddress);
    console.log("Selected Courier:", selectCourier);
    console.log("CSRF Token:", csrfToken);


    fetch("{{ url('/save-input') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            customerPhone,
            customerName,
            customerAddress,
            selectCourier
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data);
        // Show success message or handle response
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@stack('js')
</body>
</html>