<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<!-- TODO: Add Google analytics -->
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="author" content="Mehluli Hikwa" />
	<title><?php the_title() ?> | <?php bloginfo('name'); ?></title>
	<meta name="description" content="<?php bloginfo('description'); ?>" />
	<link rel="icon" type="image/png" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon.png" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<main>
		<section class="desktop-header">
			<div class="container-fluid">
				<div class="row">
					<div class="col-2">
						<a href="<?php echo home_url(); ?>">
							<img class="desktop-logo" src="<?php echo get_stylesheet_directory_uri(); ?>/img/zifa-full-crest.png" />
						</a>
					</div>



					<div class="col-10">
						<div class="d-flex flex-column align-items-end ">
							<!-- Social links (right/top) - hidden on small screens -->
							<div class="header-links-wrap">
								<div class="mb-2 mt-2 d-none d-md-block text-end">
									<span>
										<a href="https://axcentium.co.zw/" class="btn btn-primary me-3" target="_blank" rel="noopener noreferrer" style="font-size: .7rem; position:relative; top:-5px;">
											Tip-Offs
										</a>

										<a class="social" data-bs-toggle="tooltip" data-bs-placement="top"
											data-bs-title="Follow us on X"
											href="<?php echo esc_url(carbon_get_theme_option('zifa_website_custom_tw')); ?>" target="_blank" rel="noopener">
											<i class="bi bi-twitter-x"></i>
										</a>
										<a class="social" data-bs-toggle="tooltip" data-bs-placement="top"
											data-bs-title="Link us on Facebook"
											href="<?php echo esc_url(carbon_get_theme_option('zifa_website_custom_fb')); ?>" target="_blank" rel="noopener">
											<i class="bi bi-facebook"></i>
										</a>
										<a class="social" data-bs-toggle="tooltip" data-bs-placement="top"
											data-bs-title="Follow us on Instagram"
											href="<?php echo esc_url(carbon_get_theme_option('zifa_website_custom_in')); ?>" target="_blank" rel="noopener">
											<i class="bi bi-instagram"></i>
										</a>
										<a class="social" data-bs-toggle="tooltip" data-bs-placement="top"
											data-bs-title="Follow us on YouTube"
											href="<?php echo esc_url(carbon_get_theme_option('zifa_website_custom_yt')); ?>" target="_blank" rel="noopener">
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

								<nav class="menu external-menu w-100">
									<?php
									$args = array(
										'theme_location' => 'external_menu',
										'name' => 'external_menu',
										'container' => false,
										'menu_class' => 'text-end mb-0'
									);
									wp_nav_menu($args);
									?>
								</nav>
							</div>
						</div>
					</div>

					<div class="col-12">
						<nav class="menu float-end">
							<?php
							$args = array(
								'theme_location' => "main_menu",
								'name' => "main_menu",
							);
							echo wp_nav_menu($args);
							?>
						</nav>
					</div>
				</div>
			</div>
		</section>


		<section class="mobile-header">
			<header>
				<div class="conatiner-fluid">
					<div class="row">
						<div class="col-8">
							<a href="<?php echo home_url(); ?>">
								<img class="mobile-logo" src="<?php echo get_stylesheet_directory_uri(); ?>/img/zifa-word-mark.svg" />
							</a>
						</div>
						<div class="col-4">
							<a class="mobile-nav-trigger" href="#mobile-nav">
								Mobile Trigger
								<span></span>
							</a>
						</div>
					</div>
				</div>
			</header>

			<nav class="mobile-nav">
				<?php
				$args = array(
					'theme_location' => "mobile_menu",
					'name' => "mobile_menu",
					'menu_class' => "mobile-nav",
					'menu_id' => "mobile-nav",
				);
				wp_nav_menu($args);
				?>

				<ul>
					<li><a href="https://axcentium.co.zw/" class="btn btn-primary me-3" target="_blank" rel="noopener noreferrer" style="font-size: .7rem; position:relative; top:-5px;">
							Tip-Offs
						</a>
					</li>
				</ul>

			</nav>

		</section>