<?php $__env->startSection('content'); ?>
	<section class="section-name bg mt-2">
		<div class="container">
			<div class="card lk-product-detail-card">
				<div class="row no-gutters single-product">
					<div class="col-md-5">
						<div id="single-product-galary" class="slider-home-banner carousel slide" data-ride="carousel">
							<div class="carousel-inner rounded">
								<div class="carousel-item active">
									<a href="javascript:void(0)" data-toggle="modal" data-target="#lightbox">
										<img src="<?php echo e(\App\Support\PublicMedia::productOriginalUrl($product->productImage)); ?>" alt="First slide" class="img-responsive img-thumbnail">
									</a>
								</div>
								<?php $__currentLoopData = $product->media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index =>$photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<div class="carousel-item">
    								<a href="javascript:void(0)" data-toggle="modal" data-target="#lightbox">
    									<img src="<?php echo e(\App\Support\PublicMedia::productOriginalUrl($photo->url)); ?>" alt="First slide" class="img-responsive img-thumbnail">
    								</a>
								</div>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php echo $__env->make('website.partials.product_badge', ['product' => $product], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

							</div>
							<ol class="carousel-indicators">
								<li data-target="#single-product-galary" data-slide-to="0" class="active">
									<img class="img-thumbnail" alt="" src="<?php echo e(\App\Support\PublicMedia::productOriginalUrl($product->productImage)); ?>">
								</li>
								<?php $__currentLoopData = $product->media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index =>$photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<li data-target="#single-product-galary" data-slide-to="<?php echo e($index+1); ?>">
								    <img class="img-thumbnail" alt="" src="<?php echo e(\App\Support\PublicMedia::productOriginalUrl($photo->url)); ?>">
								</li>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								
							</ol>
						</div>
					</div>
					<div class="col-md-7 border-left">
						<div class="content-body">
							<h4 class="title"><?php echo e($product->productName); ?></h4>
							<div class="row">
							    
                                <?php if($product->colors->isNotEmpty()): ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label d-block font-weight-bolder">Select Color</label>
                                        <div class="btn-group-toggle" data-toggle="buttons">
                                            <?php $__currentLoopData = $product->colors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <label class="btn btn-outline-secondary <?php echo e($index === 0 ? 'active' : ''); ?>">
                                                    <input 
                                                        type="radio" 
                                                        name="color" 
                                                        id="color-<?php echo e($color->id); ?>" 
                                                        value="<?php echo e($color->id); ?>" 
                                                        autocomplete="off" 
                                                        <?php echo e($index === 0 ? 'checked' : ''); ?>

                                                    >
                                                    <?php echo e($color->colorName); ?>

                                                </label>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if($product->sizes->isNotEmpty()): ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label d-block font-weight-bolder">Select Size</label>
                                        <div class="btn-group-toggle" data-toggle="buttons">
                                            <?php $__currentLoopData = $product->sizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <label class="btn btn-outline-secondary <?php echo e($index === 0 ? 'active' : ''); ?>">
                                                    <input 
                                                        type="radio" 
                                                        name="size" 
                                                        id="size-<?php echo e($size->id); ?>" 
                                                        value="<?php echo e($size->id); ?>" 
                                                        autocomplete="off" 
                                                        <?php echo e($index === 0 ? 'checked' : ''); ?>

                                                    >
                                                    <?php echo e($size->sizeName); ?>

                                                </label>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if($product->options->isNotEmpty()): ?>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label d-block font-weight-bolder">Select Option</label>
                                        <div class="btn-group-toggle" data-toggle="buttons">
                                            <?php $__currentLoopData = $product->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <label class="btn btn-outline-secondary <?php echo e($index === 0 ? 'active' : ''); ?>">
                                                    <input 
                                                        type="radio" 
                                                        name="option" 
                                                        id="option-<?php echo e($option->id); ?>" 
                                                        value="<?php echo e($option->id); ?>" 
                                                        autocomplete="off" 
                                                        <?php echo e($index === 0 ? 'checked' : ''); ?>

                                                         onchange="
                                                            document.getElementById('product-price').textContent = '৳ ' + Number(<?php echo e((float) $option->pivot->price); ?>).toLocaleString('en-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                                        "
                                                    >
                                                    <?php echo e($option->optionName); ?>

                                                </label>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div> <!-- options row -->
                            <?php
                                $detailStock = $product->isSoldOut() ? 0 : $product->availableStock();
                                $detailCartCount = (int) \Gloudemans\Shoppingcart\Facades\Cart::content()->filter(function ($item) use ($product) {
                                    return (int) $item->id === (int) $product->id;
                                })->sum('qty');
                                $detailAddableStock = max(0, $detailStock - $detailCartCount);
                                $detailCannotAdd = $product->isSoldOut() || $detailAddableStock === 0;
                            ?>
                            <div class="leatherket-purchase-panel">
                                <div class="leatherket-inventory mb-3" role="status">
                                    <span class="lk-product-code">Product Code: <?php echo e($product->productCode); ?></span>
                                    <?php if($product->isSoldOut()): ?>
                                        <span class="leatherket-stock-label leatherket-stock-empty"><i class="fa fa-times" aria-hidden="true"></i> Stock: 0 pcs (Sold out)</span>
                                    <?php else: ?>
                                        <span class="leatherket-stock-label"><i class="fa fa-check" aria-hidden="true"></i> Available stock: <?php echo e(number_format($detailStock)); ?> pcs</span>
                                    <?php endif; ?>
                                    <span class="leatherket-stock-extra" id="lk-detail-cart-count">In your cart: <?php echo e($detailCartCount); ?> pcs | Can add: <?php echo e($detailAddableStock); ?> pcs</span>
                                </div>
                                <div class="leatherket-detail-price mb-4" id="product-price"><?php echo $product->htmlPrice(); ?></div>
                                <label for="leatherket-product-quantity" class="leatherket-quantity-label">Quantity:</label>
                                <div class="input-group input-spinner leatherket-quantity-spinner mb-4">
                                    <div class="input-group-prepend">
                                        <button class="btn btn-light lk-qty-btn" type="button" data-type="minus" data-field="quantity" aria-label="Decrease quantity" <?php echo e($detailCannotAdd ? 'disabled' : ''); ?>>−</button>
                                    </div>
                                    <input type="number" id="leatherket-product-quantity" class="form-control lk-qty-input" value="1" name="quantity" min="1" max="<?php echo e(max(1, min(10, $detailAddableStock))); ?>" inputmode="numeric" aria-label="Quantity" <?php echo e($detailCannotAdd ? 'disabled' : ''); ?>>
                                    <div class="input-group-append">
                                        <button class="btn btn-light lk-qty-btn" type="button" data-type="plus" data-field="quantity" aria-label="Increase quantity" <?php echo e($detailCannotAdd ? 'disabled' : ''); ?>>+</button>
                                    </div>
                                </div>
                                <div class="single-product-buttons leatherket-purchase-buttons">
                                    <button type="button" <?php echo e($detailCannotAdd ? 'disabled' : ''); ?> class="btn btn-primary leatherket-add-cart" onclick="addToCart(<?php echo e($product->id); ?>)">Add to cart</button>
                                    <button type="button" <?php echo e($detailCannotAdd ? 'disabled' : ''); ?> class="btn btn-outline-primary leatherket-buy-now" onclick="buyNow(<?php echo e($product->id); ?>)">Buy it now</button>
                                    <?php if((int) $product->isFreeDelivery === 1): ?>
                                        <button type="button" <?php echo e($detailCannotAdd ? 'disabled' : ''); ?>

                                                class="btn leatherket-free-delivery-order"
                                                onclick="buyNow(<?php echo e($product->id); ?>)">
                                            ফ্রী ডেলিভারিতে অর্ডার করুন
                                        </button>
                                    <?php endif; ?>
                                </div>
                                    <?php
                                        $waDigits = preg_replace('/\D+/', '', (string) Settings::get('whatsapp_number'));
                                        if (strlen($waDigits) === 11 && substr($waDigits, 0, 1) === '0') $waDigits = '88'.$waDigits;
                                        $callNumber = trim((string) Settings::get('phone_number'));
                                    ?>
                                    <?php if((Settings::get('lk_whatsapp_button_enabled') === '1' && $waDigits) || (Settings::get('lk_call_button_enabled') === '1' && $callNumber)): ?>
                                        <div class="lk-contact-actions">
                                            <?php if(Settings::get('lk_whatsapp_button_enabled') === '1' && $waDigits): ?>
                                                <a class="lk-contact-action" href="<?php echo e('https://wa.me/'.$waDigits.'?text='.rawurlencode('I want to know about '.$product->productName.' - '.url('/product/'.$product->productSlug))); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>
                                            <?php endif; ?>
                                            <?php if(Settings::get('lk_call_button_enabled') === '1' && $callNumber): ?>
                                                <a class="lk-contact-action" href="tel:<?php echo e(preg_replace('/[^0-9+]/', '', $callNumber)); ?>"><i class="fa fa-phone" aria-hidden="true"></i> Call Now</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                            </div>
                            <div class="col-sm-12 col-md-12  col-xs-12" style="padding: 0">
                                <?php echo Settings::get('product_bottom_text'); ?>

                            </div>
                            <?php
                                $shipingCharges = App\ShippingCharge::all();
                            ?>
                            <?php if($shipingCharges->isNotEmpty()): ?>
                            <table class="table mt-2">
								<tbody>
                                
                                    <?php $__currentLoopData = $shipingCharges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $shipingCharge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
									  <td>
										 <?php echo e($shipingCharge->name); ?>

									  </td>
									  <td>
										 <b>৳  <?php echo e($shipingCharge->charge); ?></b>
									  </td>
								   </tr>
                                        
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
							 </table>
                            <?php else: ?>
                                <p>No shipping charges available.</p>
                            <?php endif; ?>
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
                                <?php echo $product->productDetails; ?>

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
                <?php $__currentLoopData = $relatedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="lk-related-product">
                        <?php echo $__env->make('website.partials.product_card', ['product' => $prod], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
        value:"<?php echo e($product->productSalePrice); ?>",
        ecommerce: {
            items: [{
                item_name: "<?php echo e($product->productName); ?>", // Product Name
                item_id: "<?php echo e($product->id); ?>", // Product ID
                price: "<?php echo e($product->productSalePrice); ?>", // Product Price
                item_category: "<?php echo e($product->category->name ?? 'Uncategorized'); ?>", // Product Category
                item_variant: "<?php echo e($product->colors->first()->colorName ?? ''); ?> <?php echo e($product->sizes->first()->sizeName ?? ''); ?>", // Default Variant
                quantity: 1,
                currency: "BDT"
            }]
        }
    });
    </script>
    <script>
    fbq('track', 'ViewContent', {
        content_ids: ["<?php echo e($product->id); ?>"], // Product ID
        content_name: "<?php echo e($product->productName); ?>", // Product Name
        content_category: "<?php echo e($product->category->name ?? 'Uncategorized'); ?>", // Category
        value: "<?php echo e($product->productSalePrice); ?>", // Price
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('website.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\public_html leatherket\resources\views/website/product.blade.php ENDPATH**/ ?>