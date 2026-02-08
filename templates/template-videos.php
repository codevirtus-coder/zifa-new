<?php
/**
 * Template Name: Videos Template
 * Template Post Type: page
 * Description: ZIFA Videos landing page
 */
get_header();
?>

<?php get_template_part( 'templates/banner' );?>

<section class="container mb-5 mt-5">
	<div class="row g-4 mt-2">
		<?php
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

			$args = array(
				'post_type'      => 'zifa-videos',
				'posts_per_page' => 6,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'paged'          => $paged, 
			);
			$video_loop = new WP_Query($args);
		?>
		<?php if ( $video_loop->have_posts() ) : ?>
			<?php while ( $video_loop->have_posts() ) : $video_loop->the_post(); ?>
			
			<div class="col-12 col-md-12 col-lg-4">
				<div class="ratio ratio-16x9">
					<?php echo carbon_get_post_meta(get_the_ID(), 'zifa_video'); ?>
				</div>
       
			</div>

			<?php endwhile; ?>
			
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<p>No videos found.</p>
		<?php endif; ?>
	</div>
</section>


<section class="container">
	<?php if ( function_exists( 'pagination' ) ) {
		pagination( $video_loop->max_num_pages );
	} ?>
</section>

<?php get_footer(); ?>
