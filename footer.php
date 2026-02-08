	<footer>
		<section class="container mt-5">
			<section class="row">
				<div class="col-12 col-md-6">
					<h5>Quick Links</h5>
					<?php echo carbon_get_post_meta(get_the_ID(), 'slider_option'); ?>
					<?php
					$args = array(
						'menu' => 'footer_menu',
						'theme_location' => 'footer_menu',
					);
					wp_nav_menu($args);
					?>
				</div>

				<div class="col-12 col-md-6">
					<h5>Social Links</h5>
					<span>
						<a class="social" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-title="Follow us on X" href="<?php echo carbon_get_theme_option('zifa_website_custom_tw'); ?>" target="_blank">
							<i class="bi bi-twitter-x"></i>
						</a>
						<a class="social" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-title="Link us on Facebook" href="<?php echo carbon_get_theme_option('zifa_website_custom_fb'); ?>" target="_blank">
							<i class="bi bi-facebook"></i>
						</a>
						<a class="social" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-title="Follow us on Instagram" href="<?php echo carbon_get_theme_option('zifa_website_custom_in'); ?>" target="_blank">
							<i class="bi bi-instagram"></i>
						</a>
						<a class="social" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-title="Follow us on Youtube" href="<?php echo carbon_get_theme_option('zifa_website_custom_yt'); ?>" target="_blank">
							<i class="bi bi-youtube"></i>
						</a>
						<a class="social" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-title="Follow us on TikTok" href="<?php echo carbon_get_theme_option('zifa_website_custom_tt'); ?>" target="_blank">
							<i class="bi bi-tiktok"></i>
						</a>
						<a class="social" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-title="Join us on WhatsApp Channel" href="<?php echo carbon_get_theme_option('zifa_website_custom_wa'); ?>" target="_blank">
							<i class="bi bi-whatsapp"></i>
						</a>
					</span>
				</div>
			</section>
		</section>

		<hr>

		<section class="container mt-4">
			<section class="row">
				<div class="col-12">
					<span class="copyright-menu">
						<?php
						$args = array(
							'menu' => 'copyright_menu',
							'theme_location' => 'copyright_menu',
						);
						wp_nav_menu($args);
						?>
					</span>
				</div>
			</section>
		</section>
	</footer>

	<section class="footer-2">
		<section class="container">
			<div class="row">
				<div class="col-12 col-md-6">
					<img class="logo" style="width: 150px !important;" src="<?php echo get_stylesheet_directory_uri(); ?>/img/zifa-word-mark.svg" />
				</div>

				<div class="col-12 col-md-6">
					<p>Zimbabwe Football Association <span id="currentYear"><?php echo date("Y"); ?></span> &copy; All Rights Reserved</p>
				</div>
			</div>
		</section>
	</section>
	</main>


	<?php wp_footer(); ?>

	</body>

	</html>