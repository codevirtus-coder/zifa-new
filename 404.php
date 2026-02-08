<?php get_header(); ?>

<?php get_template_part( 'templates/banner' );?>

<section style="
    position: relative;
    top: -3rem;
    width: 100%;
    margin: 0 auto;
    box-sizing: border-box;
    clear: both;">
    <lottie-player autoplay loop mode="normal"></lottie-player>
    <script src="<?php echo get_stylesheet_directory_uri() . '/js/lottie-player.js'; ?>"></script>
    <script>
        const pageNotFoundPlayer = document.querySelector("lottie-player");
        pageNotFoundPlayer.load("<?php echo get_stylesheet_directory_uri() . '/js/404.json'; ?>");
    </script>
</section>

<?php get_footer(); ?>