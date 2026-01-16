<?php
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('hello-child-style', get_stylesheet_uri(), ['hello-elementor'], wp_get_theme()->get('Version'));
      wp_enqueue_style(
        'fontawesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css'
    );
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

