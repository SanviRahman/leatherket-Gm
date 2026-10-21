<!DOCTYPE HTML>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="cache-control" content="max-age=604800" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?php echo e(asset('images/favicon.ico')); ?>">

    <title><?php echo e(Settings::get('site_name')); ?></title>

    <script src="<?php echo e(asset('assets/js/jquery-2.0.0.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/bootstrap.bundle.min.js')); ?>"></script>
    <link href="<?php echo e(asset('assets/css/bootstrap.css')); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(asset('assets/fonts/fontawesome/css/all.min.css')); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(asset('assets/css/ui.css')); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(asset('assets/css/responsive.css')); ?>" rel="stylesheet" type="text/css"  media="only screen and (max-width: 1200px)"  />
    <link href="<?php echo e(asset('assets/css/style.css')); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(asset('assets/css/mobile-offcanvas.css')); ?>?v=20261014-sidebar-hours-v1" rel="stylesheet" type="text/css" />
    <link type="text/css" href="<?php echo e(asset('assets/css/sweetalert2.min.css')); ?>" rel="stylesheet">
    <?php echo $__env->yieldPushContent('css'); ?>
    <!-- LEATHERKET shared Palatino typography: after vendor/theme styles. -->
    <link href="<?php echo e(asset('assets/css/leatherket-typography.css')); ?>?v=1" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(asset('assets/css/leatherket-commerce.css')); ?>?v=image-badge-fix-3" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(asset('assets/css/leatherket-desktop-header.css')); ?>?v=2" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/leatherket-collections.css')); ?>?v=20261014-2x2-grid">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/leatherket-product-cards.css')); ?>?v=2">
    <link href="<?php echo e(asset('assets/css/leatherket-font-sizes.css')); ?>?v=20261011-readable2" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(asset('assets/css/leatherket-final-features.css')); ?>?v=20261015-desktop-cart-layer-fix" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(asset('assets/css/leatherket-inventory-newsletter.css')); ?>?v=20261021-1" rel="stylesheet" type="text/css" />
    <?php echo Settings::get('facebook_pixels'); ?>

    <!-- CSS for scrolling ticker -->

</head>

<body>

<div class="lk-sticky-site-nav">
    <?php if(Settings::get('lk_topbar_enabled') !== '0'): ?>
    <div class="lk-top-announcement" role="region" aria-label="Store announcement">
        <div class="container lk-top-announcement-inner">
            <?php if(Settings::get('lk_topbar_call_enabled') !== '0' && Settings::get('phone_number')): ?>
                <a class="lk-top-call" href="tel:<?php echo e(preg_replace('/[^0-9+]/', '', (string) Settings::get('phone_number'))); ?>">
                    <i class="fa fa-phone" aria-hidden="true"></i>
                    <span>কল করুন: <?php echo e(Settings::get('phone_number')); ?></span>
                </a>
            <?php endif; ?>
            <div class="lk-top-announcement-ticker" aria-label="Store news">
                <div class="lk-top-announcement-message"><?php echo e(Settings::get('lk_topbar_text') ?: 'LEATHERKET ওয়েবসাইটে আপনাকে স্বাগতম।'); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>





    

<div class="mobile-side-menu d-lg-none hide" id="leatherket-mobile-menu" aria-hidden="true">
    <div class="side-menu-wrap">
        <div class="side-menu">
            <nav class="side-menu-list" aria-label="Product categories">
                <ul class="mobile-nav-categories">
                    <?php $category = Menu::getByName('Category Menu'); ?>
                    <?php if($category): ?>
                        <?php $__currentLoopData = $category; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <a href="<?php echo e(\App\Support\CategoryMenuUrl::resolve($menu['link'] ?? '')); ?>"><?php echo e($menu['label']); ?></a>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php
                $sidebarPhone = trim((string) Settings::get('phone_number'));
                $sidebarOpenDays = trim((string) Settings::get('footer_open_days'));
                $sidebarOpenHours = trim((string) Settings::get('footer_open_hours'));
            ?>
            <?php if($sidebarPhone !== '' || $sidebarOpenDays !== '' || $sidebarOpenHours !== ''): ?>
                <div class="side-menu-footer" aria-label="Store contact and opening hours">
                    <?php if($sidebarPhone !== ''): ?>
                        <a class="leatherket-sidebar-phone" href="tel:<?php echo e(preg_replace('/[^0-9+]/', '', $sidebarPhone)); ?>"><?php echo e($sidebarPhone); ?></a>
                    <?php endif; ?>
                    <?php if($sidebarOpenDays !== '' || $sidebarOpenHours !== ''): ?>
                        <span class="leatherket-sidebar-hours">
                            <?php echo e($sidebarOpenDays !== '' && $sidebarOpenHours !== ''
                                ? $sidebarOpenDays . ' (' . $sidebarOpenHours . ')'
                                : ($sidebarOpenDays !== '' ? $sidebarOpenDays : $sidebarOpenHours)); ?>

                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<header class="section-header">
    <section class="header-main border-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-2 col-md-4 col-6 d-flex align-items-center header-brand-column">
                    <button class="navbar-toggler menu-toggle d-lg-none d-block" type="button"
                            aria-controls="leatherket-mobile-menu" aria-expanded="false" aria-label="Open menu">
                        <span class="menu-icon-open leatherket-menu-hamburger" aria-hidden="true">
                            <span></span><span></span><span></span>
                        </span>
                        <svg class="menu-icon-close leatherket-header-icon leatherket-close-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M6 6L18 18M18 6L6 18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <div class="widget-header nav-search-box d-lg-none">
                        <a href="#" class="mobile-search-trigger" aria-label="Open search">
                            <svg class="leatherket-header-icon leatherket-search-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <circle cx="10.8" cy="10.8" r="6.8" fill="none" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M15.9 15.9L20.4 20.4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </a>
                    </div>
                    <a href="<?php echo e(url('/')); ?>" class="brand-wrap">
                        <img class="logo" src="<?php echo e(\App\Support\PublicMedia::url(''.Settings::get('site_logo'))); ?>" alt="<?php echo e(Settings::get('site_name')); ?>">
                    </a> <!-- brand-wrap.// -->
                </div>
                <div class="col-lg-9 col-md-5 col-sm-12 search-box">
                    <form id="leatherket-desktop-search-form" action="<?php echo e(url('/shop')); ?>" class="search">
                        <div class="input-group w-100">
                            <div class="d-lg-none search-box-back">
                                <button class="" type="button"><i class="fa fa-arrow-left"></i></button>
                            </div>
                            <input type="text" name="q" class="form-control" placeholder="Search products..." aria-label="Search products" value="<?php echo e(request('q')); ?>">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit" aria-label="Search products">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form> <!-- The desktop search remains visible; mobile V1 still opens this form from its search icon. -->
                </div> <!-- col.// -->
                <div class="col-lg-1 col-md-3 col-sm-6 col-6 header-cart-column">
                    <div class="widgets-wrap float-right">
                        <div class="widget-header">

                            <div class="nav-cart-box dropdown" id="cart_items">
                                <?php echo $__env->make('partials.mini_cart', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            </div>


                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</header>
<style>
    .navbar-nav{
      flex-wrap: nowrap;
      overflow-x: auto;
      white-space: nowrap;
      scrollbar-width: none;        /* Firefox */
    }
    
    .navbar-nav::-webkit-scrollbar{
      display: none;                 /* Chrome, Safari */
    }
</style>

<?php
    $headerMenuItems = collect(Menu::getByName('Header Menu') ?: [])->filter(function ($item) {
        return is_array($item) && !empty($item['label']);
    });
    if ($headerMenuItems->isEmpty()) {
        $headerMenuItems = collect(Menu::getByName('Category Menu') ?: [])
            ->filter(function ($item) {
                return is_array($item) && !empty($item['label']);
            })
            ->unique(function ($item) {
                // Existing database includes duplicate category entries; show each only once in the desktop bar.
                return mb_strtolower(trim((string) $item['label']), 'UTF-8') . '|' . trim((string) ($item['link'] ?? ''));
            });
    }
?>
<?php if($headerMenuItems->isNotEmpty()): ?>
<nav class="navbar navbar-main leatherket-category-navbar navbar-expand-lg d-none d-lg-block" aria-label="Product categories">
    <div class="container">
        <div class="leatherket-category-scroll" id="main_nav">
            <ul class="navbar-nav flex-row flex-nowrap">
                <?php $__currentLoopData = $headerMenuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item<?php echo e(!empty($menu['child']) ? ' dropdown' : ''); ?>">
                        <a class="nav-link" href="<?php echo e(\App\Support\CategoryMenuUrl::resolve($menu['link'] ?? '')); ?>"><?php echo e($menu['label']); ?></a>
                        <?php if(!empty($menu['child'])): ?>
                            <div class="dropdown-menu">
                                <?php $__currentLoopData = $menu['child']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a class="dropdown-item" href="<?php echo e(\App\Support\CategoryMenuUrl::resolve($child['link'] ?? '')); ?>"><?php echo e($child['label']); ?></a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>
</div>

<?php echo $__env->yieldContent('content'); ?>

<footer class="section-footer leatherket-store-footer" id="leatherket-footer">
    <div class="leatherket-footer-container">
        <div class="leatherket-footer-grid <?php echo e(Settings::get('lk_newsletter_enabled') === '0' ? 'lk-footer-without-newsletter' : ''); ?>">
            <?php if(Settings::get('lk_newsletter_enabled') !== '0'): ?>
            <section class="leatherket-newsletter" aria-labelledby="leatherket-newsletter-heading">
                <h2 id="leatherket-newsletter-heading">Newsletter</h2>
                <p id="leatherket-newsletter-description"><?php echo e(Settings::get('footer_newsletter_text') ?: 'Subscribe for exclusive offers, new arrivals and updates from Leatherket.'); ?></p>
                <?php if(session('newsletter_success')): ?>
                    <p class="leatherket-newsletter-notice" role="status"><?php echo e(session('newsletter_success')); ?></p>
                <?php endif; ?>
                <form action="<?php echo e(route('newsletter.subscribe')); ?>" method="POST" class="leatherket-newsletter-form">
                    <?php echo csrf_field(); ?>
                    <label for="leatherket-newsletter-email" class="sr-only">Email address</label>
                    <input id="leatherket-newsletter-email" name="newsletter_email" type="email" placeholder="Enter email address..." autocomplete="email" required maxlength="254" aria-describedby="leatherket-newsletter-description" value="<?php echo e(old('newsletter_email')); ?>">
                    <input type="text" name="website" class="leatherket-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
                <?php $__errorArgs = ['newsletter_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="leatherket-newsletter-error" role="alert"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </section>
            <?php endif; ?>

            <section class="leatherket-footer-links" aria-label="Quick links">
                <?php $footer = Menu::getByName('Footer Menu'); ?>
                <details class="leatherket-quick-links" id="leatherket-footer-quick-links" open>
                    <summary>Quick Links <i class="fa fa-chevron-down" aria-hidden="true"></i></summary>
                    <nav aria-label="Footer navigation">
                        <a href="<?php echo e(url('/')); ?>">Home</a>
                        <a href="<?php echo e(url('/shop')); ?>">All Products</a>
                        <?php if($footer): ?>
                            <?php $__currentLoopData = $footer; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(\App\Support\CategoryMenuUrl::resolve($menu['link'] ?? '')); ?>"><?php echo e($menu['label']); ?></a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </nav>
                </details>
            </section>

            <section class="leatherket-footer-contact" aria-label="Contact information">
                <h3 class="leatherket-contact-heading">Contact</h3>
                <address class="leatherket-contact-details">
                    <?php if(Settings::get('footer_contact_email')): ?>
                        <a href="mailto:<?php echo e(Settings::get('footer_contact_email')); ?>"><?php echo e(Settings::get('footer_contact_email')); ?></a>
                    <?php endif; ?>
                    <?php if(Settings::get('phone_number')): ?>
                        <a href="tel:<?php echo e(Settings::get('phone_number')); ?>"><?php echo e(Settings::get('phone_number')); ?></a>
                    <?php endif; ?>
                    <?php if(Settings::get('footer_contact_address')): ?>
                        <p><?php echo e(Settings::get('footer_contact_address')); ?></p>
                    <?php endif; ?>
                    <?php
                        $footerOpenDays = trim((string) Settings::get('footer_open_days'));
                        $footerOpenHours = trim((string) Settings::get('footer_open_hours'));
                    ?>
                    <?php if($footerOpenDays !== '' || $footerOpenHours !== ''): ?>
                        <p class="leatherket-business-hours">
                            <?php echo e($footerOpenDays !== '' && $footerOpenHours !== ''
                                ? $footerOpenDays . ' (' . $footerOpenHours . ')'
                                : ($footerOpenDays !== '' ? $footerOpenDays : $footerOpenHours)); ?>

                        </p>
                    <?php endif; ?>
                </address>
                <div class="leatherket-footer-social" aria-label="Social media">
                    <?php $__currentLoopData = ['social_facebook' => ['fab fa-facebook-f', 'Facebook'], 'social_instagram' => ['fab fa-instagram', 'Instagram'], 'social_twitter' => ['fab fa-twitter', 'Twitter'], 'social_linkedin' => ['fab fa-linkedin-in', 'LinkedIn']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $social): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(Settings::get($key)): ?>
                            <a href="<?php echo e(Settings::get($key)); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo e($social[1]); ?>"><i class="<?php echo e($social[0]); ?>" aria-hidden="true"></i></a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        </div>

        <div class="leatherket-footer-brand">
            <span class="leatherket-footer-rule" aria-hidden="true"></span>
            <a href="<?php echo e(url('/')); ?>" aria-label="Leatherket home"><img src="<?php echo e(\App\Support\PublicMedia::url(''.Settings::get('site_logo'))); ?>" alt="<?php echo e(Settings::get('site_name')); ?>"></a>
            <span class="leatherket-footer-rule" aria-hidden="true"></span>
        </div>
        <div class="leatherket-footer-bottom">
            <span><?php echo e(trim((string) Settings::get('footer_copyright_text')) ?: ('Copyright © ' . date('Y') . '. Leatherket')); ?></span>
            <a href="#leatherket-footer" class="leatherket-back-top" aria-label="Back to top"><i class="fa fa-angle-up" aria-hidden="true"></i></a>
        </div>
        <div class="leatherket-payment-strip"><img src="<?php echo e(asset('assets/images/ssl.png')); ?>" alt="Supported payment providers" loading="lazy"></div>
    </div>
</footer>
<script>
(function () {
    'use strict';
    var footerLinks = document.getElementById('leatherket-footer-quick-links');
    if (!footerLinks || !window.matchMedia) return;
    var desktopView = window.matchMedia('(min-width: 992px)');
    function syncFooterLinks() {
        footerLinks.open = desktopView.matches;
    }
    footerLinks.querySelector('summary').addEventListener('click', function (event) {
        if (desktopView.matches) event.preventDefault();
    });
    syncFooterLinks();
    if (desktopView.addEventListener) {
        desktopView.addEventListener('change', syncFooterLinks);
    } else if (desktopView.addListener) {
        desktopView.addListener(syncFooterLinks);
    }
    var scrollToTop = document.querySelector('#leatherket-footer .leatherket-back-top');
    if (scrollToTop) {
        scrollToTop.addEventListener('click', function (event) {
            event.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
})();
</script>
<script src="<?php echo e(asset('assets/js/lazyload.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/sweetalert2.min.js')); ?>"></script>
<script src="<?php echo e(asset('assets/js/script.js')); ?>?v=mobile-nav-2"></script>
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
            url: "<?php echo e(url('/mini-cart-view')); ?>",
            contentType: "application/json",
            success: function (response) {
                $('#cart_items').empty().html(response);
               
            }
        });
    }

    function removeFromCart(key) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            },
            type: "DELETE",
            url: "<?php echo e(url('/checkout')); ?>/" + key,
            data: {
                '_token': '<?php echo e(csrf_token()); ?>'
            },
            contentType: "application/json",
            success: function (response) {

                showFrontendAlert('success', 'Successfully Product Removed from Cart');
                updateNavCart();
                if(response['reload'] === 'true'){
                    location.reload();
                } else if ($('.orderDetails').length) {
                    updateQuantity(0, { value: 0 });
                }

            }
        });
    }

    function updateQuantity(key, element){
        var quantity = (element && typeof element === 'object' && typeof element.value !== 'undefined') ? element.value : element;
        $.post("<?php echo e(route('updateQuantity')); ?>", {
            _token: '<?php echo e(csrf_token()); ?>',
            key: key,
            quantity: quantity
        }, function(data){
            updateNavCart();
            $('.orderDetails').html(data);
        }).fail(function(xhr) {
            showFrontendAlert('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to update cart quantity.');
            // Restore server state without erasing checkout contact inputs.
            $.post("<?php echo e(route('updateQuantity')); ?>", {
                _token: '<?php echo e(csrf_token()); ?>', key: 0, quantity: 0
            }, function(html) {
                $('.orderDetails').html(html);
                updateNavCart();
            });
        });
    }
    function updateCartOptions(key, value, optionType) {
      
        $.get("<?php echo e(route('updateCartOptions')); ?>", {
            _token: '<?php echo e(csrf_token()); ?>', // Include CSRF token
            key: key,
            [optionType] : value
        }, function(data) {
            updateNavCart(); // Update the navigation cart
            $('.orderDetails').html(data); // Update the order details with the new cart HTML
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('Error updating cart options:', textStatus, errorThrown);
            alert('Could not update cart options. Please try again.');
        });
    }

    function addToCart(id) {
        var colorId = $('input[name="color"]:checked').val();
        var sizeId = $('input[name="size"]:checked').val();
        var optionId = $('input[name="option"]:checked').val();
        var quantity = $('input[name="quantity"]').val() || 1;
        $.post("<?php echo e(url('/checkout')); ?>", {
            _token: '<?php echo e(csrf_token()); ?>',
            id: id,
            quantity: quantity,
            color_id: colorId,
            size_id: sizeId,
            option_id: optionId
        }, function (data) {
            console.log(data.message);
            showFrontendAlert(data.status, data.message);
            updateNavCart();
            if (data.status === 'success' && $('#lk-detail-cart-count').length) {
                var remaining = Math.max(0, Number(data.available_stock) - Number(data.in_cart));
                $('#lk-detail-cart-count').text('In your cart: ' + data.in_cart + ' pcs | Can add: ' + remaining + ' pcs');
                $('#leatherket-product-quantity').attr('max', Math.max(1, Math.min(10, remaining))).val(1);
                if (remaining === 0) {
                    $('.leatherket-purchase-panel .lk-qty-btn, .leatherket-purchase-panel .lk-qty-input, .leatherket-purchase-buttons button').prop('disabled', true);
                }
            }
        }).fail(function (xhr) {
            showFrontendAlert('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Could not add product to cart.');
        });
    }

    function buyNow(id) {
        var colorId = $('input[name="color"]:checked').val();
        var sizeId = $('input[name="size"]:checked').val();
        var quantity = $('input[name="quantity"]').val() || 1;
        var optionId = $('input[name="option"]:checked').val();
       // Check if color and size are selected
        if ($('#color-select').length && !colorId) {
            showFrontendAlert('error', 'Please select a color.');
            return;
        }

        if ($('#size-select').length && !sizeId) {
            showFrontendAlert('error', 'Please select a size.');
            return;
        }
        $.post("<?php echo e(url('/checkout')); ?>", {
            _token: '<?php echo e(csrf_token()); ?>',
            id: id,
            quantity: quantity,
            color_id: colorId,
            size_id: sizeId,
            option_id: optionId
        }, function (data) {
            showFrontendAlert(data.status, data.message);
            updateNavCart();
            if(data.status == 'success'){
                window.location.href = '<?php echo e(url('/checkout')); ?>';
            }
        }).fail(function (xhr) {
            showFrontendAlert('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Could not proceed to checkout.');
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
<?php echo $__env->yieldPushContent('js'); ?>
<script src="<?php echo e(asset('assets/js/leatherket-collections.js')); ?>?v=20261014-2x2-grid"></script>
</body>
</html><?php /**PATH D:\public_html leatherket\resources\views/website/layout.blade.php ENDPATH**/ ?>