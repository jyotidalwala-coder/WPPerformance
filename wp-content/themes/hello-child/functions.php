<?php
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('hello-child-style', get_stylesheet_uri(), ['hello-elementor'], wp_get_theme()->get('Version'));
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
add_shortcode('postview', 'postview_category_layout_shortcode');