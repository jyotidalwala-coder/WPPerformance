<?php
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('hello-child-style', get_stylesheet_uri(), ['hello-elementor'], wp_get_theme()->get('Version'));
    if(is_page('contact-us'))  {
    wp_enqueue_style(
        'fontawesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css'
    );
    }


});


function postview_category_layout_shortcode($atts) {

    $atts = shortcode_atts([
        'layout' => 'carousel', // grid | carousel
    ], $atts);

    $categories = get_categories();
    ob_start();

    foreach ($categories as $cat) {

        // Query inside category loop
        $query = new WP_Query([
            'post_type'      => 'post',
            'cat'            => $cat->term_id,
            'posts_per_page' => 5,
        ]);

        echo '<h3 class="elementor-heading-title ">' . esc_html($cat->name) . '</h3><hr style="margin-bottom:20px;"/>';
        echo '<div class="elementor-posts elementor-grid elementor-' . esc_attr($atts['layout']) . '">';

        while ($query->have_posts()) {
            $query->the_post();
            ?>
            <div class="elementor-post elementor-grid-item elementor-grid-item-inline">
                <div class="elementor-post__card">
                    <?php if (has_post_thumbnail()): ?>
                        <div class="elementor-post__thumbnail">
                            <?php the_post_thumbnail('medium'); ?>
                        </div>
                    <?php endif; ?>
                    <div class="elementor-post__text">
                        <h3 class="elementor-post__title"><?php the_title(); ?></h3>
                    </div>
                </div>
            </div>
            <?php
        }

        echo '</div>';
        wp_reset_postdata();
    }

       

    return ob_get_clean();
}
add_shortcode('postview', 'postview_category_inline_cached_shortcode');

function postview_category_inline_cached_shortcode() {

   
    $cache_key = 'postview_inline_elementor_cards_v3';
    $cached = get_transient($cache_key);

    if ($cached !== false) {
        return $cached;
    }

    // Single optimized query: get all posts
    $query = new WP_Query([
        'post_type'              => 'post',
        'posts_per_page'         => -1,
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]);

    if (!$query->have_posts()) {
        return '';
    }

    // Group posts by category
    $grouped = [];

    while ($query->have_posts()) {
        $query->the_post();
        $categories = get_the_category();
        if (!empty($categories)) {
            foreach ($categories as $cat) {
                $grouped[$cat->term_id]['name'] ??= $cat->name; // PHP 7.4+ null coalesce assignment
                $grouped[$cat->term_id]['posts'][] = get_post(); // store WP_Post object
            }
        }
    }
   

    ob_start();

    foreach ($grouped as $cat) {
        echo '<h3 class="elementor-heading-title">' . esc_html($cat['name']) . '</h3>';
        echo '<hr style="margin-bottom:20px;">';
        echo '<div class="elementor-posts elementor-grid">';

        // Show max 5 posts per category
        $posts_to_show = array_slice($cat['posts'], 0, 5);

        foreach ($posts_to_show as $post_obj) {
            // **Make sure global $post is set**
            global $post;
            $post = $post_obj;
            setup_postdata($post);

            ?>
            <div class="elementor-post elementor-grid-item elementor-grid-item-inline">
                <div class="elementor-post__card">

                    <?php if (has_post_thumbnail($post->ID)): ?>
                        <div class="elementor-post__thumbnail">
                            <a href="<?php the_permalink(); ?>">
                                <?php echo get_the_post_thumbnail($post->ID, 'medium'); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="elementor-post__text">
                        <h3 class="elementor-post__title">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h3>
                    </div>

                </div>
            </div>
            <?php
        }

        echo '</div>';
        wp_reset_postdata();
    }

    $output = ob_get_clean();

    // Cache for 1 hour
    set_transient($cache_key, $output, HOUR_IN_SECONDS);

    return $output;
}
//add_shortcode('postview', 'postview_category_inline_cached_shortcode');

add_filter('script_loader_tag', function ($tag, $handle) {
    if ($handle === 'swiper') {
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}, 10, 2);

add_filter('wp_get_attachment_image_attributes', function ($attr) {
    $attr['loading'] = 'lazy';
    $attr['decoding'] = 'async';
    return $attr;
});

// Shortcode: Secure Category Slider

function secure_postview_slider_shortcode() {

    // Nonce for AJAX security
    $nonce = wp_create_nonce('postview_slider_nonce');

    // Get all categories
    $categories = get_categories(['hide_empty' => true]);

    ob_start(); ?>
    
    <div class="postview-slider-wrapper">

        <!-- Category filter buttons -->
        <div class="postview-filters mb-3">
            <button class="postview-filter-btn" data-cat="0">All</button>
            <?php foreach ($categories as $cat): ?>
                <button class="postview-filter-btn" data-cat="<?php echo esc_attr($cat->term_id); ?>">
                    <?php echo esc_html($cat->name); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Container for AJAX-loaded sliders -->
        <div id="postview-slider-container"></div>

        <!-- Hidden nonce -->
        <input type="hidden" id="postview-slider-nonce" value="<?php echo esc_attr($nonce); ?>">

    </div>

    <script>
    jQuery(document).ready(function($){
        function loadPosts(catId){
            var nonce = $('#postview-slider-nonce').val();

            $.ajax({
                url: '<?php echo esc_url(admin_url("admin-ajax.php")); ?>',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'postview_slider_ajax',
                    category: catId,
                    security: nonce
                },
                beforeSend: function(){
                    $('#postview-slider-container').html('<p>Loading posts...</p>');
                },
                success: function(response){
                    if(response.success){
                        $('#postview-slider-container').html(response.data);

                        // Initialize Swiper for each slider
                        document.querySelectorAll('.swiper').forEach(function(swiperEl){
                            new Swiper(swiperEl, {
                                slidesPerView: 1,
                                spaceBetween: 20,
                                lazy: true,
                                loop: false,
                                pagination: {
                                    el: swiperEl.querySelector('.swiper-pagination'),
                                    clickable: true
                                },
                                navigation: {
                                    nextEl: swiperEl.querySelector('.swiper-button-next'),
                                    prevEl: swiperEl.querySelector('.swiper-button-prev')
                                },
                                observer: true,
                                observeParents: true
                            });
                        });
                    } else {
                        $('#postview-slider-container').html('<p>' + response.data + '</p>');
                    }
                },
                error: function(){
                    $('#postview-slider-container').html('<p>An error occurred while loading posts.</p>');
                }
            });
        }

        // Initial load (All posts)
        loadPosts(0);

        // Filter click
        $('.postview-filter-btn').on('click', function(){
            var cat = $(this).data('cat');
            loadPosts(cat);
        });
    });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('secure_postview_slider', 'secure_postview_slider_shortcode');

add_action('wp_ajax_postview_slider_ajax', 'secure_postview_slider_ajax');
add_action('wp_ajax_nopriv_postview_slider_ajax', 'secure_postview_slider_ajax');

function secure_postview_slider_ajax() {

    // CSRF protection
    check_ajax_referer('postview_slider_nonce', 'security');

    $cat_id = isset($_POST['category']) ? intval($_POST['category']) : 0;
    if ($cat_id < 0) {
        wp_send_json_error('Invalid category.');
    }

    // Cache key per category
    $cache_key = 'postview_slider_cat_' . $cat_id;
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        wp_send_json_success($cached);
    }

    // Query posts
    $args = [
        'post_type' => 'post',
        'posts_per_page' => 5,
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ];
    if ($cat_id > 0) {
        $args['cat'] = $cat_id;
    }

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        wp_send_json_error('No posts found.');
    }

    // Build HTML safely using post objects, no global $post
    $html = '<div class="swiper mySwiper-' . esc_attr($cat_id) . '"><div class="swiper-wrapper">';
    
    foreach ($query->posts as $post_obj) {
        $title = get_the_title($post_obj->ID);
        $permalink = get_permalink($post_obj->ID);
        $img_url = get_the_post_thumbnail_url($post_obj->ID, 'medium');
        
        $html .= '<div class="swiper-slide">';
        $html .= '<div class="elementor-post elementor-post__card">';
        if ($img_url) {
            $html .= '<div class="elementor-post__thumbnail">';
            $html .= '<a href="' . esc_url($permalink) . '">';
            $html .= '<img src="' . esc_url($img_url) . '" loading="lazy" alt="' . esc_attr($title) . '" class="img-fluid">';
            $html .= '</a></div>';
        }
        $html .= '<div class="elementor-post__text">';
        $html .= '<h3 class="elementor-post__title"><a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a></h3>';
        $html .= '</div></div></div>';
    }
    
    $html .= '</div><div class="swiper-pagination"></div><div class="swiper-button-next"></div><div class="swiper-button-prev"></div></div>';

    // Cache HTML for 1 hour
    set_transient($cache_key, $html, HOUR_IN_SECONDS);

    wp_send_json_success($html);
}
