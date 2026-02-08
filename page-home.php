<?php get_header(); ?>


<?php get_template_part('templates/template-slider-home'); ?>


<?php get_template_part('templates/loop-news'); ?>


<!-- <section class="green-section"> -->

<section class="green-section" style="background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/img/default/banner-image.jpg">
	<section class="container mb-5 mt-5">
		<div class="row">
			<div class="col-12 col-md-8">
				<h2 class="heading-text">Upcoming Matches</h2>
			</div>
			<div class="col-12 col-md-4 d-none d-md-block float-end text-end">
				<a href="<?php echo home_url('fixtures') ?>" class="btn btn-primary">All Matches</a>
			</div>
		</div>

		<div class="row">
			<?php get_template_part('templates/loop-fixtures'); ?>
		</div>
	</section>
	<section class="container mb-5">
		<div class="row">

			<div class="col-12">
				<h2 class="heading-text">Teams</h2>
			</div>

			<div class="col-12 col-md-6 col-lg-3 mb-5">
				<div class="card teams">
					<h3 class="card-title">Warriors</h3>
					<a href="<?php echo home_url('warriors') ?>">
						<img class="img-fluid" src="<?php echo get_stylesheet_directory_uri(); ?>/img/team/team-warriors.png" />
					</a>
					<a href="<?php echo home_url('warriors') ?>" class="card-link">View Team</a>
				</div>
			</div>

			<div class="col-12 col-md-6 col-lg-3 mb-5">
				<div class="card teams">
					<h3 class="card-title">Mighty Warriors</h3>
					<a href="<?php echo home_url('mighty-warriors') ?>">
						<img class="img-fluid" src="<?php echo get_stylesheet_directory_uri(); ?>/img/team/team-mighty-warriors.png" />
					</a>
					<a href="<?php echo home_url('mighty-warriors') ?>" class="card-link">View Team</a>
				</div>
			</div>

			<div class="col-12 col-md-6 col-lg-3 mb-5">
				<div class="card teams">
					<h3 class="card-title">U20</h3>
					<a href="<?php echo home_url('u20') ?>">
						<img class="img-fluid" src="<?php echo get_stylesheet_directory_uri(); ?>/img/team/team-u20.png" />
					</a>
					<a href="<?php echo home_url('u20') ?>" class="card-link">View Team</a>
				</div>
			</div>

			<div class="col-12 col-md-6 col-lg-3 mb-5">
				<div class="card teams">
					<h3 class="card-title">U17</h3>
					<a href="<?php echo home_url('u17') ?>">
						<img class="img-fluid" src="<?php echo get_stylesheet_directory_uri(); ?>/img/team/team-u17.png" />
					</a>
					<a href="<?php echo home_url('u17') ?>" class="card-link">View Team</a>
				</div>
			</div>
		</div>
	</section>
</section>


<section class="home-about">
	<div class="container">

		<?php the_content(); ?>

	</div>
</section>


<!-- About -->

<!-- <section class="container my-5">
  <div class="row ">

    <div class="col-12 col-lg-6 mb-4 mb-lg-0">
      <img
        src="<?php echo esc_url(get_template_directory_uri() . '/img/zifa-agm.png'); ?>"
        alt="ZIFA AGM"
        class="img-fluid w-100  about-image"
      >
    </div>

   
    <div class="col-12 col-lg-6">
      <h2 class="h2 fw-bold mb-3">About</h2>
      <p class="mb-4">
        The Zimbabwe Football Association (ZIFA) was established in 1965 as the governing body of football in Zimbabwe, formed to oversee and develop the game nationwide and to create unity within the sport across all communities.
      </p>

      <a href="<?php echo esc_url(home_url('/about')); ?>"
         class="btn btn-primary">
        READ MORE   
      </a>
    </div>
  </div>
</section> -->

<section class="container mb-5">
	<div class="row">
		<div class="col-12 col-md-8">
			<h2 class="heading-text">Video Gallery</h2>
		</div>
		<div class="col-12 col-md-4 d-none d-md-block float-end text-end">
			<a href="<?php echo home_url('video-gallery') ?>" class="btn btn-primary">All Videos</a>
		</div>
	</div>

	<div class="row g-4 mt-2">
		<?php
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$args = array(
			'post_type'      => 'zifa-videos',
			'posts_per_page' => 3,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
		$video_loop = new WP_Query($args);
		?>
		<?php if ($video_loop->have_posts()) : ?>
			<?php while ($video_loop->have_posts()) : $video_loop->the_post(); ?>

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





<section class="our-partners">
	<div class="container">
		<section class="row">
			<div class="col-12">
				<h2 class="heading-text float-start">Our Partners & Advertisers</h2>
			</div>
		</section>

		<section class="row row-cols-1 row-cols-md-3 g-4">
			<div class="zifaPartners swiper">
				<div class="swiper-wrapper">
					<?php
					$args = array(
						'post_type' => 'partners-slider',
						'posts_per_page' => -1,
						'orderby' => 'menu_order', // Order by the "menu_order" field
						'order' => 'ASC' // Order in ascending order (lower number first)
					);
					$partner_query = new WP_Query($args);
					?>

					<?php if ($partner_query->have_posts()) : ?>
						<?php while ($partner_query->have_posts()) : $partner_query->the_post(); ?>

							<div class="swiper-slide">
								<img class="img-thumbnail" alt="<?php the_title(); ?>" src="<?php echo the_post_thumbnail_url(); ?>" />
							</div>

						<?php endwhile; ?>

						<?php wp_reset_postdata(); ?>
					<?php else : ?>
					<?php endif; ?>
				</div>
			</div>
		</section>
	</div>
</section>

<?php get_footer(); ?>