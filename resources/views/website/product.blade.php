@extends('website.layout')
@section('content')
	<section class="section-name bg mt-2">
		<div class="container">
			<div class="card lk-product-detail-card">
				<div class="row no-gutters single-product">
					<div class="col-md-5">
						<div id="single-product-galary" class="slider-home-banner carousel slide" data-ride="carousel">
							<div class="carousel-inner rounded">
								<div class="carousel-item active">
									<a href="javascript:void(0)" data-toggle="modal" data-target="#lightbox">
										<img src="{{ \App\Support\PublicMedia::productOriginalUrl($product->productImage)  }}" alt="First slide" class="img-responsive img-thumbnail">
									</a>
								</div>
								@foreach($product->media as $index =>$photo)
								<div class="carousel-item">
    								<a href="javascript:void(0)" data-toggle="modal" data-target="#lightbox">
    									<img src="{{ \App\Support\PublicMedia::productOriginalUrl($photo->url)  }}" alt="First slide" class="img-responsive img-thumbnail">
    								</a>
								</div>
								@endforeach
                                @include('website.partials.product_badge', ['product' => $product])

							</div>
							<ol class="carousel-indicators">
								<li data-target="#single-product-galary" data-slide-to="0" class="active">
									<img class="img-thumbnail" alt="" src="{{ \App\Support\PublicMedia::productOriginalUrl($product->productImage)  }}">
								</li>
								@foreach($product->media as $index =>$photo)
								<li data-target="#single-product-galary" data-slide-to="{{$index+1}}">
								    <img class="img-thumbnail" alt="" src="{{ \App\Support\PublicMedia::productOriginalUrl($photo->url)  }}">
								</li>
								@endforeach
								
							</ol>
						</div>
					</div>
					<div class="col-md-7 border-left">
						<div class="content-body">
							<h4 class="title">{{ $product->productName  }}</h4>
							<div class="row">
							    
                                @if ($product->colors->isNotEmpty())
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label d-block font-weight-bolder">Select Color</label>
                                        <div class="btn-group-toggle" data-toggle="buttons">
                                            @foreach ($product->colors as $index => $color)
                                                <label class="btn btn-outline-secondary {{ $index === 0 ? 'active' : '' }}">
                                                    <input 
                                                        type="radio" 
                                                        name="color" 
                                                        id="color-{{ $color->id }}" 
                                                        value="{{ $color->id }}" 
                                                        autocomplete="off" 
                                                        {{ $index === 0 ? 'checked' : '' }}
                                                    >
                                                    {{ $color->colorName }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($product->sizes->isNotEmpty())
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label d-block font-weight-bolder">Select Size</label>
                                        <div class="btn-group-toggle" data-toggle="buttons">
                                            @foreach ($product->sizes as $index => $size)
                                                <label class="btn btn-outline-secondary {{ $index === 0 ? 'active' : '' }}">
                                                    <input 
                                                        type="radio" 
                                                        name="size" 
                                                        id="size-{{ $size->id }}" 
                                                        value="{{ $size->id }}" 
                                                        autocomplete="off" 
                                                        {{ $index === 0 ? 'checked' : '' }}
                                                    >
                                                    {{ $size->sizeName }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if ($product->options->isNotEmpty())
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label d-block font-weight-bolder">Select Option</label>
                                        <div class="btn-group-toggle" data-toggle="buttons">
                                            @foreach ($product->options as $index => $option)
                                                <label class="btn btn-outline-secondary {{ $index === 0 ? 'active' : '' }}">
                                                    <input 
                                                        type="radio" 
                                                        name="option" 
                                                        id="option-{{ $option->id }}" 
                                                        value="{{ $option->id }}" 
                                                        autocomplete="off" 
                                                        {{ $index === 0 ? 'checked' : '' }}
                                                         onchange="
                                                            document.getElementById('product-price').textContent = '৳ ' + Number({{ (float) $option->pivot->price }}).toLocaleString('en-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                                        "
                                                    >
                                                    {{ $option->optionName }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                            </div> <!-- options row -->
                            @php
                                $detailStock = $product->isSoldOut() ? 0 : $product->availableStock();
                                $detailCartCount = (int) \Gloudemans\Shoppingcart\Facades\Cart::content()->filter(function ($item) use ($product) {
                                    return (int) $item->id === (int) $product->id;
                                })->sum('qty');
                                $detailAddableStock = max(0, $detailStock - $detailCartCount);
                                $detailCannotAdd = $product->isSoldOut() || $detailAddableStock === 0;
                            @endphp
                            <div class="leatherket-purchase-panel">
                                <div class="leatherket-inventory mb-3" role="status">
                                    <span class="lk-product-code">Product Code: {{ $product->productCode }}</span>
                                    @if($product->isSoldOut())
                                        <span class="leatherket-stock-label leatherket-stock-empty"><i class="fa fa-times" aria-hidden="true"></i> Stock: 0 pcs (Sold out)</span>
                                    @else
                                        <span class="leatherket-stock-label"><i class="fa fa-check" aria-hidden="true"></i> Available stock: {{ number_format($detailStock) }} pcs</span>
                                    @endif
                                    <span class="leatherket-stock-extra" id="lk-detail-cart-count">In your cart: {{ $detailCartCount }} pcs | Can add: {{ $detailAddableStock }} pcs</span>
                                </div>
                                <div class="leatherket-detail-price mb-4" id="product-price">{!! $product->htmlPrice() !!}</div>
                                <label for="leatherket-product-quantity" class="leatherket-quantity-label">Quantity:</label>
                                <div class="input-group input-spinner leatherket-quantity-spinner mb-4">
                                    <div class="input-group-prepend">
                                        <button class="btn btn-light lk-qty-btn" type="button" data-type="minus" data-field="quantity" aria-label="Decrease quantity" {{ $detailCannotAdd ? 'disabled' : '' }}>−</button>
                                    </div>
                                    <input type="number" id="leatherket-product-quantity" class="form-control lk-qty-input" value="1" name="quantity" min="1" max="{{ max(1, min(10, $detailAddableStock)) }}" inputmode="numeric" aria-label="Quantity" {{ $detailCannotAdd ? 'disabled' : '' }}>
                                    <div class="input-group-append">
                                        <button class="btn btn-light lk-qty-btn" type="button" data-type="plus" data-field="quantity" aria-label="Increase quantity" {{ $detailCannotAdd ? 'disabled' : '' }}>+</button>
                                    </div>
                                </div>
                                <div class="single-product-buttons leatherket-purchase-buttons">
                                    <button type="button" {{ $detailCannotAdd ? 'disabled' : '' }} class="btn btn-primary leatherket-add-cart" onclick="addToCart({{ $product->id }})">Add to cart</button>
                                    <button type="button" {{ $detailCannotAdd ? 'disabled' : '' }} class="btn btn-outline-primary leatherket-buy-now" onclick="buyNow({{ $product->id }})">Buy it now</button>
                                    @if((int) $product->isFreeDelivery === 1)
                                        <button type="button" {{ $detailCannotAdd ? 'disabled' : '' }}
                                                class="btn leatherket-free-delivery-order"
                                                onclick="buyNow({{ $product->id }})">
                                            ফ্রী ডেলিভারিতে অর্ডার করুন
                                        </button>
                                    @endif
                                </div>
                                    @php
                                        $waDigits = preg_replace('/\D+/', '', (string) Settings::get('whatsapp_number'));
                                        if (strlen($waDigits) === 11 && substr($waDigits, 0, 1) === '0') $waDigits = '88'.$waDigits;
                                        $callNumber = trim((string) Settings::get('phone_number'));
                                    @endphp
                                    @if((Settings::get('lk_whatsapp_button_enabled') === '1' && $waDigits) || (Settings::get('lk_call_button_enabled') === '1' && $callNumber))
                                        <div class="lk-contact-actions">
                                            @if(Settings::get('lk_whatsapp_button_enabled') === '1' && $waDigits)
                                                <a class="lk-contact-action" href="{{ 'https://wa.me/'.$waDigits.'?text='.rawurlencode('I want to know about '.$product->productName.' - '.url('/product/'.$product->productSlug)) }}" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>
                                            @endif
                                            @if(Settings::get('lk_call_button_enabled') === '1' && $callNumber)
                                                <a class="lk-contact-action" href="tel:{{ preg_replace('/[^0-9+]/', '', $callNumber) }}"><i class="fa fa-phone" aria-hidden="true"></i> Call Now</a>
                                            @endif
                                        </div>
                                    @endif
                            </div>
                            <div class="col-sm-12 col-md-12  col-xs-12" style="padding: 0">
                                {!!  Settings::get('product_bottom_text')  !!}
                            </div>
                            @php
                                $shipingCharges = App\ShippingCharge::all();
                            @endphp
                            @if ($shipingCharges->isNotEmpty())
                            <table class="table mt-2">
								<tbody>
                                
                                    @foreach ($shipingCharges as $index => $shipingCharge)
                                    <tr>
									  <td>
										 {{ $shipingCharge->name }}
									  </td>
									  <td>
										 <b>৳  {{ $shipingCharge->charge }}</b>
									  </td>
								   </tr>
                                        
                                    @endforeach
                                </tbody>
							 </table>
                            @else
                                <p>No shipping charges available.</p>
                            @endif
                        </div>
                    </div>
				</div>
            
			<div class="card mt-2">
				<div class="card-body">
					<div class="tabs tabs--style-2">
						<ul class="bor-rtop-lr  nav nav-tabs sticky-top bg-white">
							<li class="nav-item">
								<a href="#tab_default_1" data-toggle="tab" class="nav-link text-uppercase strong-600 active show">Description</a>
							</li>
						</ul>
						<div class="tab-content pt-0">
							<div class="tab-pane active show p-2" id="tab_default_1">
                                {!! $product->productDetails !!}
							</div>
						  </div>
					</div>
				</div>
			</div>
		</div>
	</section>
    <section class="section-name bg padding-y-sm">
        <div class="container">
            <header class="section-heading">
                <h4 class="section-title">Related products</h4>
            </header>
            <div class="lk-related-products" aria-label="Related products; scroll horizontally">
                @foreach($relatedProducts as $prod)
                    <div class="lk-related-product">
                        @include('website.partials.product_card', ['product' => $prod])
                    </div>
                @endforeach
            </div>
        </div>
    </section> 

    <div id="lightbox" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-middle">
            <div class="modal-content">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <div class="modal-body">
                    <img class="img-responsive img-thumbnail" src="" alt="" />
                </div>
            </div>
        </div>
    </div>
    <script>
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        event: "view_item",
        value:"{{ $product->productSalePrice }}",
        ecommerce: {
            items: [{
                item_name: "{{ $product->productName }}", // Product Name
                item_id: "{{ $product->id }}", // Product ID
                price: "{{ $product->productSalePrice }}", // Product Price
                item_category: "{{ $product->category->name ?? 'Uncategorized' }}", // Product Category
                item_variant: "{{ $product->colors->first()->colorName ?? '' }} {{ $product->sizes->first()->sizeName ?? '' }}", // Default Variant
                quantity: 1,
                currency: "BDT"
            }]
        }
    });
    </script>
    <script>
    fbq('track', 'ViewContent', {
        content_ids: ["{{ $product->id }}"], // Product ID
        content_name: "{{ $product->productName }}", // Product Name
        content_category: "{{ $product->category->name ?? 'Uncategorized' }}", // Category
        value: "{{ $product->productSalePrice }}", // Price
        currency: "BDT"
    });
    </script>

<script>
    (function () {
        var input = document.querySelector('#leatherket-product-quantity');
        if (!input) return;
        var holder = input.closest('.leatherket-quantity-spinner');
        holder.querySelectorAll('.lk-qty-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                if (input.disabled) return;
                var min = Number(input.min) || 1;
                var max = Number(input.max) || 10;
                var value = Math.min(max, Math.max(min, Number(input.value) || 1));
                input.value = Math.min(max, Math.max(min, value + (button.dataset.type === 'plus' ? 1 : -1)));
            });
        });
        input.addEventListener('change', function () {
            input.value = Math.min(Number(input.max) || 10, Math.max(1, Number(input.value) || 1));
        });
    }());
</script>
@endsection
