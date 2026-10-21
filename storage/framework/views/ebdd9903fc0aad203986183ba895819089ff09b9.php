<?php if(Cart::count() > 0): ?>
    <a href="#" class="icon icon-xs rounded-circle border" data-toggle="dropdown">
        <i class="fa fa-shopping-cart d-inline-block nav-box-icon desktop-cart-icon" aria-hidden="true"></i>
        <svg class="mobile-cart-bag-icon d-lg-none" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M6.7 8.7H17.3L18.3 20H5.7L6.7 8.7Z" fill="none" stroke="currentColor" stroke-width="1.35" stroke-linejoin="round"/>
            <path d="M9 9V7.2C9 5.45 10.34 4 12 4C13.66 4 15 5.45 15 7.2V9" fill="none" stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>
        </svg>
        <span class="badge badge-pill badge-danger notify"><?php echo e(Cart::count()); ?></span>
    </a>
    <ul class="dropdown-menu dropdown-menu-right px-0">
        <li>
            <div class="dropdown-cart px-0">
                <div class="dc-header"><h4 class="text-center py-2">Cart Items (<?php echo e(Cart::count()); ?>)</h4></div>
                <div class="dropdown-cart-items c-scrollbar">
                    <?php $__currentLoopData = Cart::content(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="dc-item">
                            <div class="d-flex align-items-center">
                                <div class="dc-image">
                                    <a href="<?php echo e(url('/product/'.$item->model->productSlug)); ?>">
                                        <img src="<?php echo e(\App\Support\PublicMedia::url('product/thumbnail/'.$item->model->productImage)); ?>" class="img-fluid">
                                    </a>
                                </div>
                                <div class="dc-content">
                                    <span class="d-block dc-product-name text-capitalize strong-600 mb-1">
                                        <a href="<?php echo e(url('/product/'.$item->model->productSlug)); ?>"><?php echo e($item->model->productName); ?></a>
                                    </span>
                                    
                                    <?php if($item->options->colorName): ?>
                                        <small class="text-muted">Color: <?php echo e($item->options->colorName); ?>,</small>
                                    <?php endif; ?>
                                    <?php if($item->options->sizeName): ?> 
                                        <small class="text-muted">Size: <?php echo e($item->options->sizeName); ?>,</small>
                                    <?php endif; ?>
                                    
                                    <span class="dc-quantity">Quantity: <?php echo e($item->qty); ?></span>
                                    <?php if($item->model): ?>
                                        <small class="lk-cart-stock">Stock: <?php echo e($item->model->isSoldOut() ? 0 : $item->model->availableStock()); ?> pcs</small>
                                    <?php endif; ?>
                                    <span class="dc-price">TK <?php echo e($item->price); ?></span>
                                </div>
                                <div class="dc-actions">
                                    <button onclick="removeFromCart('<?php echo e($item->rowId); ?>')"><i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="dc-item py-3">
                    <span class="subtotal-text">Subtotal</span>
                    <span class="subtotal-amount">৳ <?php echo e(Cart::subtotal(0, '', '')); ?></span>
                </div>
                <div class="p-2 text-center dc-btn">
                    <a href="<?php echo e(url('/checkout')); ?>" class="btn btn-success btn-block">Checkout</a>
                </div>
            </div>
        </li>
    </ul>
<?php else: ?>
    <a href="<?php echo e(url('/checkout')); ?>" class="icon icon-xs rounded-circle border" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
        <i class="fa fa-shopping-cart d-inline-block nav-box-icon desktop-cart-icon" aria-hidden="true"></i>
        <svg class="mobile-cart-bag-icon d-lg-none" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M6.7 8.7H17.3L18.3 20H5.7L6.7 8.7Z" fill="none" stroke="currentColor" stroke-width="1.35" stroke-linejoin="round"/>
            <path d="M9 9V7.2C9 5.45 10.34 4 12 4C13.66 4 15 5.45 15 7.2V9" fill="none" stroke="currentColor" stroke-width="1.35" stroke-linecap="round"/>
        </svg>
        <span class="badge badge-pill badge-danger notify">0</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-right px-0" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(-328px, 32px, 0px);">
        <li>
            <div class="dropdown-cart px-0">
                <div class="dc-header">
                    <h4 class="text-center py-2">Empty Cart</h4>
                </div>
            </div>
        </li>
    </ul>
<?php endif; ?><?php /**PATH D:\public_html leatherket\resources\views/partials/mini_cart.blade.php ENDPATH**/ ?>