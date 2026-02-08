<?php
/**
 * Template Name: News Template
 * Description: For creating News Landing Page (choose categories by slug)
 **/
get_header();
?>

<?php get_template_part( 'templates/banner' );?>

<section class="container mt-5 mb-5">
  <div class="row row-cols-1 row-cols-md-3 g-4">
  <?php
    $paged = ( get_query_var( 'paged' ) ) ? intval( get_query_var( 'paged' ) ) : 1;

    // --- put the category slugs you want to show (leave empty to use page slug) ---
    $cat_slugs = array(
    //   "latest-news",
	  //  "press-releases",
    //    "inside-zifa",
    );

   
    $cat_ids = array();
    if ( ! empty( $cat_slugs ) ) {
      foreach ( $cat_slugs as $slug ) {
        $term = get_term_by( 'slug', sanitize_title( $slug ), 'category' );
        if ( $term && ! is_wp_error( $term ) ) {
          $cat_ids[] = (int) $term->term_id;
        }
      }
    }

    // fallback: if no $cat_slugs provided, try to use the page slug as category slug
    if ( empty( $cat_ids ) ) {
      $page_id = get_queried_object_id();
      if ( $page_id ) {
        $page_slug = get_post_field( 'post_name', $page_id );
        if ( $page_slug ) {
          $term = get_term_by( 'slug', sanitize_title( $page_slug ), 'category' );
          if ( $term && ! is_wp_error( $term ) ) {
            $cat_ids[] = (int) $term->term_id;
          }
        }
      }
    }

    // Build query args
    $args = array(
      'post_type'          => 'post',
      'posts_per_page'     => 9,
      'orderby'            => 'date',
      'order'              => 'DESC',
      'paged'              => $paged,
      'ignore_sticky_posts'=> 1,
    );

    if ( ! empty( $cat_ids ) ) {
      $args['category__in'] = $cat_ids;
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
    <div class="col-12"><p>No posts found for the selected categories.</p></div>
  <?php endif; ?>
  </div>
</section>

<section class="container mt-4">
  <?php
   
    if ( function_exists( 'pagination' ) ) {
      pagination( $news_query->max_num_pages );
    } else {
      echo paginate_links( array(
        'total'   => $news_query->max_num_pages,
        'current' => $paged,
      ) );
    }
  ?>
</section>

<?php get_footer(); ?>
