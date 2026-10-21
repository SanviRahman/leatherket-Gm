{{-- Shared collection-inspired product card: homepage, shop, category, related and AJAX. --}}
@php
    $productUrl = url('/product/'.$product->productSlug);
    $hasVariants = $product->colors->isNotEmpty()
        || $product->sizes->isNotEmpty()
        || $product->options->isNotEmpty();
    $soldOut = $product->isSoldOut();
    $stockCount = $soldOut ? 0 : $product->availableStock();
    $cartCountForProduct = (int) \Gloudemans\Shoppingcart\Facades\Cart::content()->filter(function ($item) use ($product) {
        return (int) $item->id === (int) $product->id;
    })->sum('qty');
    $freeDelivery = (int) $product->isFreeDelivery === 1;
@endphp
<article class="lk-product-card">
    <a class="lk-product-card-image" href="{{ $productUrl }}" aria-label="{{ $product->productName }}">
        <img src="{{ \App\Support\PublicMedia::productOriginalUrl($product->productImage) }}"
             alt="{{ $product->productName }}" loading="lazy">
        @include('website.partials.product_badge', ['product' => $product])
    </a>
    <div class="lk-product-card-details">
        <a href="{{ $productUrl }}" class="lk-product-card-name" title="{{ $product->productName }}">
            {{ $product->productName }}
        </a>
        <div class="lk-product-card-price">{!! $product->htmlPrice() !!}</div>
        <div class="lk-product-card-stock" aria-label="Stock availability">
            @if($soldOut)
                <span class="lk-stock-out">Stock: 0 pcs</span>
            @else
                <span>Stock: {{ number_format($stockCount) }} pcs</span>
            @endif
            @if($cartCountForProduct > 0)
                <span class="lk-cart-item-count">In cart: {{ $cartCountForProduct }}</span>
            @endif
        </div>
    </div>
    @if($soldOut)
        <button class="lk-product-card-action" type="button" disabled>Sold out</button>
    @elseif($cartCountForProduct >= $stockCount)
        <button class="lk-product-card-action" type="button" disabled>Stock limit in cart</button>
    @elseif($hasVariants)
        <a class="lk-product-card-action" href="{{ $productUrl }}">
            {{ $freeDelivery ? 'ফ্রী ডেলিভারিতে অর্ডার করুন' : 'অর্ডার করুন' }}
        </a>
    @else
        <button class="lk-product-card-action" type="button" onclick="buyNow({{ $product->id }})">
            {{ $freeDelivery ? 'ফ্রী ডেলিভারিতে অর্ডার করুন' : 'অর্ডার করুন' }}
        </button>
    @endif
</article>
