<?php get_header(); ?>


<?php get_template_part( 'templates/banner' );?>


<section class="container">
	<div class="row g-4">
		<div class="col-12 col-md-9 mt-5">
			<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail('full', array('class' => 'img-fluid mt-2 mb-3'));
				} else {
					// leave empty
				}
			?>
			<?php the_content();?>
		</div>

		<div class="col-12 col-md-3 mt-5">
			<h2>More News</h2>

			<?php
				$current_post_id = get_the_ID();
				$args = array(
					'post_type' => 'post', 
					'posts_per_page' => 3, 
					'orderby' => 'date', 
					'order' => 'DESC',
					'post__not_in' => array($current_post_id), // Exclude current post
				);
				$news_query = new WP_Query($args); 
			?>

			<?php if ( $news_query->have_posts() ) : ?>
				<?php $counter = 0; // Initialize counter ?>
				
				<?php while ( $news_query->have_posts() ) : $news_query->the_post(); ?>
				
					<?php if ($counter == 0) : ?>
						<!-- First post -->
						<div id="<?php the_ID(); ?>" class="card">
							<?php
								if ( has_post_thumbnail() ) {
									the_post_thumbnail('full', array('class' => 'img-fluid'));
								} else {
									echo '<img class="img-fluid" alt="' . esc_attr(get_the_title()) . '" src="' . esc_url(get_stylesheet_directory_uri()) . '/img/default/default-image.jpg" />';
								}
							?>
							<div class="card-body">
								<a href="<?php the_permalink(); ?>">
									<h4 class="card-title"><?php the_title(); ?></h4>
								</a>
								<p class="date-pill">
									<?php echo get_the_date('D j M Y'); ?>							
								</p>
								<div class="card-text"><?php the_excerpt(); ?></div>
								<a href="<?php the_permalink(); ?>" class="btn btn-primary">Read More</a>
							</div>
						</div>

					<?php else : ?>
						<!-- Next two posts -->
						<div id="<?php the_ID(); ?>" class="card">
							<div class="card-body">
								<a href="<?php the_permalink(); ?>">
									<h4 class="card-title"><?php the_title(); ?></h4>
								</a>
								<p class="date-pill">
									<?php echo get_the_date('D j M Y'); ?>							
								</p>
								<a href="<?php the_permalink(); ?>" class="btn btn-primary">Read More</a>
							</div>
						</div>
					<?php endif; ?>

					<?php $counter++; // Increment counter ?>
				
				<?php endwhile; ?>
				
				<?php wp_reset_postdata(); ?>
			<?php endif; ?>

		</div>
	</div>
</section>


<?php get_footer(); ?>