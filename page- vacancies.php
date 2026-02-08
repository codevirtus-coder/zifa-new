<?php get_header(); ?>

<?php get_template_part( 'templates/banner' ); ?>

<section class="jobs-page py-5">
    <div class="container">
        <div class="jobs-wrapper">
            <article class="job-card mb-4">
                <div class="row g-0">

                    <div class="col-md-9 job-left">
                 
                        <div class="job-header p-3 border-bottom">
                            <p class="mb-0 ">
                                <span class ="text-primary fw-semibold">Job Title:</span> Mining Electrician
                                <span class="mx-1">|</span>
                                <span class ="text-primary fw-semibold">Due Date:</span> 5 Dec 2025
                            </p>
                        </div>
                   
                        <div class="job-meta p-3 border-bottom">
                            <div class="d-flex flex-wrap gap-4 mb-2">
                                <span class="d-flex align-items-center">
                                    <i class="bi  text-primary  bi-building me-2"></i> Jena Mines
                                </span>
                                <span class="d-flex align-items-center">
                                    <i class="bi text-primary  bi-geo-alt-fill me-2"></i> Silovela, Gokwe
                                </span>
                                <span class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-text me-2 text-primary "></i> Permanent
                                </span>
                            </div>

                            <div class="d-flex flex-wrap gap-4">
                                <span class="d-flex align-items-center">
                                    <i class="bi text-primary  bi-clock-history me-2"></i> Full time
                                </span>
                                <span class="d-flex align-items-center">
                                    <i class="bi text-primary  bi-calendar-event me-2"></i> Published: 3 days ago
                                </span>
                            </div>
                        </div>

                
                        <div class="job-body p-3">
                            <p class="mb-3 job-desc">
                                Leading KMH owned mining company operating in Zimbabwe seeks a qualified electrician
                                with experience working on mine sites. Based in Zimbabwe this will be a permanent
                                position – either relocate or work on a rotation basis.
                            </p>
                            <a href="#" class=" text-primary text-decoration-none">
                                Read More
                            </a>
                        </div>
                    </div>

                    <div class="col-md-3 d-flex align-items-center justify-content-center job-logo-col">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/jena-mine.jpg"
                             class="img-fluid job-logo"
                             alt="<?php bloginfo( 'name' ); ?>">
                    </div>

                </div>
            </article>

    
            <article class="job-card mb-4">
                <div class="row g-0">

                  
                    <div class="col-md-9 job-left">
                     
                        <div class="job-header p-3 border-bottom">
                            <p class="mb-0 ">
                                <span class ="text-primary fw-semibold">Job Title:</span> HR Officer
                                <span class="mx-1">|</span>
                                <span class ="text-primary fw-semibold">Due Date:</span> 29 Nov 2025
                            </p>
                        </div>

                    
                        <div class="job-meta p-3 border-bottom">
                            <div class="d-flex flex-wrap gap-4 mb-2">
                                <span class="d-flex align-items-center">
                                    <i class="bi text-primary  bi-building me-2"></i> Shamva Mine
                                </span>
                                <span class="d-flex align-items-center">
                                    <i class="bi text-primary  bi-geo-alt-fill me-2"></i> Bindura-Shamva Greenstone Belt
                                </span>
                                <span class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-text me-2 text-primary "></i> Associate / Permanent
                                </span>
                            </div>

                            <div class="d-flex flex-wrap gap-4">
                                <span class="d-flex align-items-center">
                                    <i class="bi text-primary bi-clock-history me-2"></i> Full time
                                </span>
                                <span class="d-flex align-items-center">
                                    <i class="bi text-primary  bi-calendar-event me-2"></i> Published: 3 days ago
                                </span>
                            </div>
                        </div>

                     
                        <div class="job-body p-3">
                            <p class="mb-3 job-desc">
                                Shamva Mine is situated in the Bindura-Shamva Greenstone Belt,
                                90km northeast of Harare, Zimbabwe.
                            </p>
                            <a href="#" class=" text-primary  text-decoration-none">
                                Read More
                            </a>
                        </div>
                    </div>

                 
                    <div class="col-md-3 d-flex align-items-center justify-content-center job-logo-col">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/jena-mine.jpg"
                             class="img-fluid job-logo"
                             alt="<?php bloginfo( 'name' ); ?>">
                    </div>

                </div>
            </article>

        </div>
    </div>
</section>

<?php get_footer(); ?>
