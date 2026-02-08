<section class="swiperHome swiper">
	<div class="swiper-wrapper">
		<?php
            $args = array(
                'post_type' => 'zifa-slider',
                'posts_per_page' => 4,   
			    'post_status'         => 'publish',    
                'orderby'             => 'date',       
                'order'               => 'DESC',
                'orderby' => 'menu_order', // Order by the "menu_order" field
                'order' => 'ASC' // Order in ascending order (lower number first)
            );
            $slider_query = new WP_Query($args); 
        ?>
        <?php if ( $slider_query->have_posts() ) : ?>
            <?php while ( $slider_query->have_posts() ) : $slider_query->the_post(); ?>

			<section class="swiper-slide" style="background-image: url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>');">
				<div class="container">
					<div class="row">
						<div class="col-12 col-md-8 col-lg-6 slider-content">

                            <?php
                            // get both checkboxes
                            $sliderLink = carbon_get_post_meta(get_the_ID(), 'slider_option');
                            $sliderBg   = carbon_get_post_meta(get_the_ID(), 'slider_background');
                            ?>

                            <?php if ($sliderBg) { ?>

                                <!-- If "Remove options background" is checked: only show the button -->
                                <?php
                                if ($sliderLink = true) {
                                    ?>
                                    <a class="btn btn-primary" href="<?php echo carbon_get_post_meta(get_the_ID(), 'slider_btn_url'); ?>" target="_blank">
                                        <?php echo carbon_get_post_meta(get_the_ID(), 'slider_btn_text'); ?>
                                    </a>
                                    <?php
                                } else {
                                    ?>
                                    <a class="btn btn-primary" href="<?php echo home_url()?><?php echo carbon_get_post_meta(get_the_ID(), 'slider_btn_url'); ?>">
                                        <?php echo carbon_get_post_meta(get_the_ID(), 'slider_btn_text'); ?>
                                    </a>
                                <?php
                                }
                                ?>

                            <?php } else { ?>

                                <!-- Normal card: title + text + button -->
								<div class="card">
									<div class="card-body">

										<h1><?php the_title(); ?></h1>
										<p class="card-text">
											<?php echo carbon_get_post_meta(get_the_ID(), 'slider_home_text'); ?>
										</p>
                                        <?php

										if ($sliderLink = true) {
											?>
											<a class="btn btn-primary" href="<?php echo carbon_get_post_meta(get_the_ID(), 'slider_btn_url'); ?>">
												<?php echo carbon_get_post_meta(get_the_ID(), 'slider_btn_text'); ?>
											</a>
											<?php
										} else {
											?>
											<a class="btn btn-primary" href="<?php echo home_url()?><?php echo carbon_get_post_meta(get_the_ID(), 'slider_btn_url'); ?>" target="_blank">
												<?php echo carbon_get_post_meta(get_the_ID(), 'slider_btn_text'); ?>
											</a>
										<?php
										}
										?>
										
									</div>
								</div>

                            <?php } ?>

						</div>
					</div>
				</div>
				<div class="overlay"></div>
			</section>

            <?php endwhile; ?>

            <?php wp_reset_postdata(); ?>
            <?php else : ?>
        <?php endif; ?>
	</div>

	<div class="swiper-pagination"></div>
	<!-- <div class="swiper-button-prev"></div> -->
	<!-- <div class="swiper-button-next"></div> -->
</section>
