<?php
    $selectedIds = collect(explode(',', (string) Settings::get('lk_collection_category_ids')))
        ->filter(fn ($id) => ctype_digit((string) $id))->map(fn ($id) => (int) $id)->unique()->values();
    $collectionCategories = $selectedIds->isNotEmpty()
        ? \App\Category::whereIn('id', $selectedIds->all())->get()->sortBy(fn ($category) => $selectedIds->search((int) $category->id))
        : collect();
?>
<?php if(Settings::get('lk_collection_enabled') === '1' && $collectionCategories->isNotEmpty()): ?>
<section class="lk-collections" aria-labelledby="lk-collections-heading">
    <div class="lk-collections-inner">
        <header class="lk-collections-heading">
            <small><?php echo e(Settings::get('lk_collection_eyebrow') ?: 'THE LEATHERKET EDIT'); ?></small>
            <h2 id="lk-collections-heading"><?php echo e(Settings::get('lk_collection_heading') ?: 'Shop by Collection'); ?></h2>
            <?php if(Settings::get('lk_collection_subtitle')): ?><p><?php echo e(Settings::get('lk_collection_subtitle')); ?></p><?php endif; ?>
        </header>
        
        <div class="lk-collections-track" id="lk-collections-track" tabindex="0"
             role="region" aria-roledescription="carousel" aria-label="Shop by Collection">
            <?php $__currentLoopData = $collectionCategories->chunk(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $collectionPage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="lk-collection-page" role="group" aria-label="Collection page <?php echo e($loop->iteration); ?> of <?php echo e((int) ceil($collectionCategories->count() / 4)); ?>">
                    <?php $__currentLoopData = $collectionPage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a class="lk-collection-item" href="<?php echo e(url('/category/'.$category->categorySlug)); ?>">
                            <div class="lk-collection-image">
                                <?php if($category->categoryImage): ?>
                                    <img loading="lazy" src="<?php echo e(\App\Support\PublicMedia::url('product/thumbnail/'.$category->categoryImage)); ?>" alt="<?php echo e($category->categoryName); ?>">
                                <?php else: ?>
                                    <div class="lk-collection-placeholder"><?php echo e($category->categoryName); ?></div>
                                <?php endif; ?>
                                <div class="lk-collection-overlay">
                                    <span>EXPLORE COLLECTION</span>
                                    <h3><?php echo e($category->categoryName); ?></h3>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="lk-collections-controls">
            <button class="lk-scroll-button" type="button" data-lk-scroll="prev" aria-controls="lk-collections-track" aria-label="Previous collections">&#8592;</button>
            <button class="lk-scroll-button" type="button" data-lk-scroll="next" aria-controls="lk-collections-track" aria-label="Next collections">&#8594;</button>
        </div>
        <?php if(Settings::get('lk_collection_description_heading') || Settings::get('lk_collection_description')): ?>
            <div class="lk-collections-description">
                <?php if(Settings::get('lk_collection_description_heading')): ?><h3><?php echo e(Settings::get('lk_collection_description_heading')); ?></h3><?php endif; ?>
                <?php if(Settings::get('lk_collection_description')): ?><p><?php echo e(Settings::get('lk_collection_description')); ?></p><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>
<?php /**PATH D:\public_html leatherket\resources\views/website/partials/shop_by_collection.blade.php ENDPATH**/ ?>