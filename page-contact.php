<?php get_header(); ?>


<?php get_template_part('templates/banner'); ?>


<section class="container">
    <div class="row my-5">
        <div class="col-12 col-md-4">
            <div class="card contact-greeting">
                <div class="contact-details">
                    <h3 class="card-title">Contact Details</h3>

                    <h5>Address</h5>
                    <p><?php echo carbon_get_theme_option('zifa_website_address'); ?></p>

                    <h5>Email</h5>
                    <p><a href="mailto:<?php echo carbon_get_theme_option('zifa_website_email'); ?>" target="_blank"><?php echo carbon_get_theme_option('zifa_website_email'); ?></a></p>

                    <h5>Phone/Mobile</h5>
                    <p><?php echo carbon_get_theme_option('zifa_website_number'); ?></p>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-8">
            <div class="card contact-form">
                <?php echo do_shortcode('[contact-form-7 id="7bbafb4" title="Contact Form"]'); ?>
            </div>
        </div>

        <div class="col-12">
            <div class="google-maps">
                <?php echo carbon_get_theme_option('zifa_website_google_map'); ?>
            </div>
        </div>
    </div>
</section>


<?php get_footer(); ?>