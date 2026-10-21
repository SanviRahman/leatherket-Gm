
<?php
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
?>
<article class="lk-product-card">
    <a class="lk-product-card-image" href="<?php echo e($productUrl); ?>" aria-label="<?php echo e($product->productName); ?>">
        <img src="<?php echo e(\App\Support\PublicMedia::productOriginalUrl($product->productImage)); ?>"
             alt="<?php echo e($product->productName); ?>" loading="lazy">
        <?php echo $__env->make('website.partials.product_badge', ['product' => $product], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </a>
    <div class="lk-product-card-details">
        <a href="<?php echo e($productUrl); ?>" class="lk-product-card-name" title="<?php echo e($product->productName); ?>">
            <?php echo e($product->productName); ?>

        </a>
        <div class="lk-product-card-price"><?php echo $product->htmlPrice(); ?></div>
        <div class="lk-product-card-stock" aria-label="Stock availability">
            <?php if($soldOut): ?>
                <span class="lk-stock-out">Stock: 0 pcs</span>
            <?php else: ?>
                <span>Stock: <?php echo e(number_format($stockCount)); ?> pcs</span>
            <?php endif; ?>
            <?php if($cartCountForProduct > 0): ?>
                <span class="lk-cart-item-count">In cart: <?php echo e($cartCountForProduct); ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php if($soldOut): ?>
        <button class="lk-product-card-action" type="button" disabled>Sold out</button>
    <?php elseif($cartCountForProduct >= $stockCount): ?>
        <button class="lk-product-card-action" type="button" disabled>Stock limit in cart</button>
    <?php elseif($hasVariants): ?>
        <a class="lk-product-card-action" href="<?php echo e($productUrl); ?>">
            <?php echo e($freeDelivery ? 'ফ্রী ডেলিভারিতে অর্ডার করুন' : 'অর্ডার করুন'); ?>

        </a>
    <?php else: ?>
        <button class="lk-product-card-action" type="button" onclick="buyNow(<?php echo e($product->id); ?>)">
            <?php echo e($freeDelivery ? 'ফ্রী ডেলিভারিতে অর্ডার করুন' : 'অর্ডার করুন'); ?>

        </button>
    <?php endif; ?>
</article>
<?php /**PATH D:\public_html leatherket\resources\views/website/partials/product_card.blade.php ENDPATH**/ ?>