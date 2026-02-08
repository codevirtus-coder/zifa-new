<section class="home-blog">
  <div class="container">
    <div class="row">
      <div class="col-12 col-md-8">
        <h2 class="heading-text">Latest News</h2>
      </div>
      <div class="col-12 col-md-4 d-none d-md-block text-end">
        <a href="<?php echo home_url('news'); ?>" class="btn btn-primary">All News</a>
      </div>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4 mt-2">
      <?php
      // --- put the category slugs you want to show (leave empty to show latest posts) ---
      $cat_slugs = array(
		"latest-news",
		"press-releases",
        "inside-zifa",
      );

      $posts_per_page = 3;

      // convert slugs -> term IDs (category)
      $cat_ids = array();
      if ( ! empty( $cat_slugs ) ) {
        foreach ( $cat_slugs as $slug ) {
          $term = get_term_by( 'slug', sanitize_title( $slug ), 'category' );
          if ( $term && ! is_wp_error( $term ) ) {
            $cat_ids[] = (int) $term->term_id;
          }
        }
      }


      if ( ! empty( $cat_ids ) ) {
        $args = array(
          'post_type'          => 'post',
          'posts_per_page'     => $posts_per_page,
          'category__in'       => $cat_ids,
          'orderby'            => 'date',
          'order'              => 'DESC',
          'ignore_sticky_posts'=> 1,
        );
      } else {
        // fallback: latest posts
        $args = array(
          'post_type'          => 'post',
          'posts_per_page'     => $posts_per_page,
          'orderby'            => 'date',
          'order'              => 'DESC',
          'ignore_sticky_posts'=> 1,
        );
      }

      $news_query = new WP_Query( $args );
      if ( $news_query->have_posts() ) :
        while ( $news_query->have_posts() ) : $news_query->the_post();
      ?>
        <div class="col-12 col-sm-6 col-md-4">
          <div id="post-<?php the_ID(); ?>" class="card h-100 d-flex flex-column">
            <div class="card-body d-flex flex-column">
              <a href="<?php the_permalink(); ?>">
                <?php
                if ( has_post_thumbnail() ) {
                  the_post_thumbnail( 'full', array( 'class' => 'img-fluid mb-3' ) );
                } else {
                  echo '<img class="img-fluid mb-3" alt="' . esc_attr( get_the_title() ) . '" src="' . esc_url( get_stylesheet_directory_uri() . '/img/default/default-image.jpg' ) . '" />';
                }
                ?>
              </a>

              <a href="<?php the_permalink(); ?>">
                <h4 class="card-title"><?php the_title(); ?></h4>
              </a>

              <div class="badge text-bg-primary mb-3 align-self-start">
                <?php echo get_the_date( 'D j M Y' ); ?>
              </div>

              <div class="card-text"><?php the_excerpt(); ?></div>

              <div class="mt-auto">
                <a href="<?php the_permalink(); ?>" class="btn btn-primary">Read More</a>
              </div>
            </div>
          </div>
        </div>
      <?php
        endwhile;
        wp_reset_postdata();
      else :
      ?>
        <div class="col-12"><p>No news found.</p></div>
      <?php endif; ?>
    </div>
  </div>
</section>
