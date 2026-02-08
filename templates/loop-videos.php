<section class="container mb-5">
	<div class="row">
		<div class="col-12 col-md-8">
			<h2 class="heading-text">ZIFA Videos</h2>
		</div>
		<div class="col-12 col-md-4 d-none d-md-block float-end text-end">
			<a href="<?php echo home_url('video-gallery')?>" class="btn btn-primary">All Videos</a>
		</div>
	</div>

	<div class="row g-4 mt-2">
		<?php
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			
			$args = array(
				'post_type'      => 'zifa-videos',
				'posts_per_page' => 3,
				'orderby'        => 'date',
				'order'          => 'DESC',
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
		<?php endif; ?>
	</div>
</section>