<?php

/**
 * 
 * Template Name: Action Zone - Results Only
 * Description: For creating Team Content Pages
 **/

get_header();
?>


<?php get_template_part('templates/banner'); ?>

<section class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12">
            <?php the_content(); ?>
        </div>
    </div>
</section>

<section class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12">

            <?php


            $post_ids = get_posts([
                'post_type'      => 'fixtures-results',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'meta_query'     => [
                    [
                        'key'     => '_fixture_match_status',
                        'value'   => 'result',
                        'compare' => '=',
                    ],
                ],
                'fields' => 'ids',
            ]);

            $results = [];
            $countries = function_exists('get_countries_from_json') ? get_countries_from_json() : [];

            if (!empty($post_ids)) {
                foreach ($post_ids as $post_id) {

                    $home = carbon_get_post_meta($post_id, 'fixture_country_home') ?: '';
                    $away = carbon_get_post_meta($post_id, 'fixture_country_away') ?: '';

                    // min_code tie-breaker
                    if ($home === '' && $away === '') $min_code = '';
                    elseif ($home === '') $min_code = $away;
                    elseif ($away === '') $min_code = $home;
                    else $min_code = (strcmp($home, $away) <= 0) ? $home : $away;

                    // date
                    $raw_date = carbon_get_post_meta($post_id, 'fixture_date') ?: '';
                    $timestamp = 0;

                    if (!empty($raw_date)) {
                        $ts = strtotime($raw_date);
                        if ($ts !== false) $timestamp = (int)$ts;
                    }

                    $date_human = 'Date TBA';
                    if ($timestamp) $date_human = date('F j, Y', $timestamp);

                    // names
                    $home_name = $home ? ($countries[$home] ?? 'Home Team') : 'Home';
                    $away_name = $away ? ($countries[$away] ?? 'Away Team') : 'Away';


                    $home_score = carbon_get_post_meta($post_id, 'fixture_country_home_score');
                    $away_score = carbon_get_post_meta($post_id, 'fixture_country_away_score');

                    $show_score = is_numeric($home_score) && is_numeric($away_score);
                    $score_text = $show_score ? ($home_score . ' - ' . $away_score) : '–';

                    $results[] = [
                        'ID'         => $post_id,
                        'min_code'   => $min_code,
                        'timestamp'  => $timestamp,
                        'date_human' => $date_human,
                        'home_code'  => $home,
                        'away_code'  => $away,
                        'home_name'  => $home_name,
                        'away_name'  => $away_name,
                        'middle'     => $score_text,
                        'permalink'  => get_permalink($post_id),
                    ];
                }

                // newest first; missing dates last; tie-break min_code asc
                usort($results, function ($a, $b) {
                    $ta = (int)$a['timestamp'];
                    $tb = (int)$b['timestamp'];

                    if ($ta > 0 && $tb > 0) {
                        if ($ta === $tb) return strcmp($a['min_code'], $b['min_code']);
                        return $tb <=> $ta;
                    }

                    if ($ta > 0 && $tb === 0) return -1;
                    if ($ta === 0 && $tb > 0) return 1;

                    return strcmp($a['min_code'], $b['min_code']);
                });
            }
            ?>


            <section class="hc-mini-panel w-100">
                <div class="hc-mini-panel__head">
                    <h3 class="hc-mini-panel__title">Results</h3>
                </div>

                <div class="hc-mini-panel__body">
                    <?php if (!empty($results)) : ?>

                        <div class="row g-3">
                            <?php foreach ($results as $m) : ?>

                                <div class="<?php echo (count($results) === 1) ? 'col-12' : 'col-12 col-md-6'; ?>">

                                    <a class="hc-mini-row hc-mini-row--tile" href="<?php echo esc_url($m['permalink']); ?>">

                                        <div class="hc-mini-row__side">
                                            <?php if (!empty($m['home_code'])) : ?>
                                                <span class="fi fi-<?php echo esc_attr($m['home_code']); ?> fis"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="<?php echo esc_attr($m['home_name']); ?>"></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="hc-mini-row__mid">
                                            <div class="hc-mini-row__date"><?php echo esc_html($m['date_human']); ?></div>
                                            <div class="hc-mini-row__meta"><?php echo esc_html($m['middle']); ?></div>
                                            <div class="hc-mini-row__match">
                                                <?php echo esc_html(strtoupper($m['home_name'] . ' v ' . $m['away_name'])); ?>
                                            </div>
                                        </div>

                                        <div class="hc-mini-row__side">
                                            <?php if (!empty($m['away_code'])) : ?>
                                                <span class="fi fi-<?php echo esc_attr($m['away_code']); ?> fis"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="<?php echo esc_attr($m['away_name']); ?>"></span>
                                            <?php endif; ?>
                                        </div>

                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php else : ?>
                        <div class="hc-mini-empty">No results yet.</div>
                    <?php endif; ?>
                </div>
            </section>

        </div>
    </div>
</section>

<?php get_footer(); ?>