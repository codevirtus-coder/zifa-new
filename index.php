<?php get_header(); ?>

<?php get_template_part( 'templates/banner' );?>

<section class="container mt-5 mb-5">
    <div class="row">
        <section class="col-12">
            <?php the_content();?>
        </section>
    </div>
</section>

<?php get_footer(); ?>