<?php

/**
 * Fetch and display up to 4 upcoming fixtures
 * - Post Type: fixtures-results
 * - Filter: fixture_match_status = 'fixture'
 * - Sort: fixture_date DESC (newest/future first)
 * - Display: Date reformatted to "D j M Y" (e.g., Mon 25 Oct 2025)
 */

// Step 1: Fetch all posts with fixture_match_status = 'fixture'
$posts = get_posts([
    'post_type'      => 'fixtures-results',
    'posts_per_page' => 10,  // Get more than needed to allow filtering
    'post_status'    => 'publish',
    'meta_query'     => [
        [
            'key'     => 'fixture_match_status',
            'value'   => 'result',
            'compare' => '=',
        ],
    ],
    'fields'         => 'ids', // Only IDs for performance
]);

// Step 2: Prepare and sort posts by fixture_date (Y-m-d) in DESC order
$sorted_fixtures = [];

foreach ($posts as $post_id) {
    $date_raw = carbon_get_post_meta($post_id, 'fixture_date'); // Stored as Y-m-d

    // Skip if date is missing
    if (empty($date_raw)) {
        continue;
    }

    // Convert Y-m-d to timestamp for sorting, store readable format
    $timestamp = strtotime($date_raw);
    $date_formatted = date('D j M Y', $timestamp); // e.g., "Mon 25 Oct 2025"

    $sorted_fixtures[] = [
        'ID'         => $post_id,
        'date_raw'   => $date_raw,      // For sorting
        'date_human' => $date_formatted, //For Display - Match Date (formatted for humans) 
        'timestamp'  => $timestamp,
    ];
}

// Sort: newest/future dates first (DESC)
usort($sorted_fixtures, function ($a, $b) {
    return $b['date_raw'] <=> $a['date_raw']; // String compare on Y-m-d works
});

// Limit to 4 fixtures
$fixtures_to_show = array_slice($sorted_fixtures, 0, 4);

// Step 3: Render fixtures or fallback message
if (!empty($fixtures_to_show)) : ?>
    <?php foreach ($fixtures_to_show as $fixture) :
        $post_id = $fixture['ID'];
        $post = get_post($post_id);
        setup_postdata($post); // Required for template tags like the_permalink()

        // Get country codes
        $home_code = carbon_get_post_meta($post_id, 'fixture_country_home');
        $away_code = carbon_get_post_meta($post_id, 'fixture_country_away');

        // Resolve country names from JSON lookup
        $countries = get_countries_from_json();
        $home_name = $home_code ? ($countries[$home_code] ?? 'Home Team') : 'Home';
        $away_name = $away_code ? ($countries[$away_code] ?? 'Away Team') : 'Away';

        // Other fixture details
        $stadium     = carbon_get_post_meta($post_id, 'fixture_stadium');
        $match_type  = carbon_get_post_meta($post_id, 'fixture_match_type');
        $kickoff     = carbon_get_post_meta($post_id, 'fixture_time');
        $permalink   = get_permalink($post_id);
    ?>
        <div class="col-12 col-md-6 col-lg-3 mb-5">
            <div class="card matches">
                <div class="card-body">
                    <h5 class="card-title"><?php echo esc_html($fixture['date_human']); ?></h5>

                    <h6 class="card-subtitle mb-2 text-body-secondary">
                        <?php echo esc_html($stadium); ?>
                    </h6>
                    <hr>

                    <h6 class="card-subtitle mb-2 text-body-secondary">
                        <?php echo esc_html($match_type); ?>
                    </h6>
                    <hr>

                    <div class="row text-center mb-5-custom margin-bottom mb-4">
                        <div class="col-4">
                            <?php if ($home_code): ?>
                                <span class="fi fi-<?php echo esc_attr($home_code); ?> fis"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-title="<?php echo esc_attr($home_name); ?>">
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="col-4">
                            <p class="card-text bg-gray-300 text-black fw-bold">
                                Kick-off<br>
                                <?php echo esc_html($kickoff ?: 'TBD'); ?>
                            </p>
                        </div>

                        <div class="col-4">
                            <?php if ($away_code): ?>
                                <span class="fi fi-<?php echo esc_attr($away_code); ?> fis"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-title="<?php echo esc_attr($away_name); ?>">
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <a href="<?php echo esc_url($permalink); ?>" class="card-link">View More</a>
            </div>
        </div>
    <?php endforeach; ?>
    <?php wp_reset_postdata(); ?>
<?php else : ?>

    <div class="col-12">
        <div class="card text-center border-0 bg-light">
            <div class="card-body">
                <p class="card-text text-muted">
                    <i class="bi bi-trophy" style="font-size: 2rem;"></i><br>
                    <strong>No match results yet.</strong><br>
                    Results will appear after matches are played.
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>