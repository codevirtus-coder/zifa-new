<?php get_header(); ?>


<?php get_template_part( 'templates/banner' );?>


<section class="container mt-5 mb-5">
	<div class="row row-cols-1 row-cols-md-3 g-4">
	<?php
		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		$args = array(
			'post_type' => 'post',
			'posts_per_page' => 9,
			'orderby' => 'date',
			'order' => 'DESC',
			'paged' => $paged
		);
		$news_query = new WP_Query($args); 
	?>
	<?php if ( $news_query->have_posts() ) : ?>
		<?php while ( $news_query->have_posts() ) : $news_query->the_post(); ?>
		
		<div class="col-12 col-sm-6 col-md-4">
			<div id="<?php the_ID(); ?>" class="card h-100 d-flex flex-column">
				<div class="card-body">
					<a href="<?php the_permalink(); ?>">
						<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail('full', array('class' => 'img-fluid mb-3'));
							} else {
								echo '<img class="img-fluid mb-3" alt="' . esc_attr(get_the_title()) . '" src="' . esc_url(get_stylesheet_directory_uri()) . '/img/default/default-image.jpg" />';
							}
						?>
					</a>
					
					<a href="<?php the_permalink(); ?>">
						<h4 class="card-title"><?php the_title(); ?></h4>
					</a>

					<div class="badge text-bg-primary mb-3">
						<?php echo get_the_date('D j M Y'); ?>
					</div>

					<div class="card-text"><?php the_excerpt(); ?></div>
					
					<a href="<?php the_permalink(); ?>" class="btn btn-primary">Read More</a>
				</div>
			</div>
		</div>

		<?php endwhile; ?>
		
		<?php wp_reset_postdata(); ?>
		<?php else : ?>
	<?php endif; ?>
	</div>
</section>


<section class="container">
	<?php if (function_exists("pagination")) {
		pagination($news_query->max_num_pages);
	} ?>
</section>


<?php get_footer(); ?>