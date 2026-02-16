<?php

/**
 * Template Name: Action Zone - Landing Page
 * Description: For creating Team Content Pages
 **/
get_header();
?>


<?php get_template_part('templates/banner'); ?>


<section class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12">
            <?php the_content(); ?>
        </div>
    </div>
</section>





<!-- <section class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Results</h2>
        </div>

        <?php get_template_part('templates/loop-results'); ?>
    </div>
</section> -->


<?php get_footer(); ?>