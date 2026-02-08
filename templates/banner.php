<?php
$post_id = get_the_ID();
$featured_banner = carbon_get_post_meta($post_id, 'featured_banner');

if ($featured_banner && has_post_thumbnail($post_id)) {
    $banner_image_url = get_the_post_thumbnail_url($post_id, 'full');
} else {
    $banner_image_url = get_template_directory_uri() . '/img/default/banner-image.jpg';
}
?>

<section class="banner" style="background-image: url('<?php echo esc_url($banner_image_url); ?>');">
    <div class="container">
        <h1>
            <?php
                if (is_category()) {
                    echo get_the_category()[0]->name;
                } else {
                    the_title();
                }
            ?>
        </h1>

        <?php
            if (function_exists('yoast_breadcrumb')) {
                yoast_breadcrumb('<p id="breadcrumbs">', '</p>');
            }
        ?>
    </div>
    <div class="overlay"></div>
</section>