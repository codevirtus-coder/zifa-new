<?php

/**
 * Template Name: Action Zone - Fixtures & Results (Grouped)
 * Description: Fixtures & results grouped by match type with per-type view.
 **/
get_header();
?>

<?php get_template_part('templates/banner'); ?>

<div class="fixtures-page">
<section class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12">
            <?php the_content(); ?>
        </div>
    </div>
</section>

<?php
$today     = wp_date('Y-m-d');
$countries = function_exists('get_countries_from_json') ? get_countries_from_json() : [];
$cal_month = (int) wp_date('n');
$cal_year  = (int) wp_date('Y');
$nonce     = wp_create_nonce('hc_calendar_nonce');

$selected_type = isset($_GET['match_type']) ? sanitize_text_field($_GET['match_type']) : '';

function zifa_normalize_match_type($value)
{
    $value = trim((string) $value);
    return $value !== '' ? $value : 'Other';
}

function zifa_match_type_slug($value)
{
    $value = zifa_normalize_match_type($value);
    return sanitize_title($value);
}

// Collect available match types
$type_posts = get_posts([
    'post_type'      => 'fixtures-results',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'fields'         => 'ids',
]);

$match_types = [];
foreach ($type_posts as $pid) {
    $type_raw = function_exists('carbon_get_post_meta') ? carbon_get_post_meta($pid, 'fixture_match_type') : '';
    $type = zifa_normalize_match_type($type_raw);
    $slug = zifa_match_type_slug($type);
    if (!isset($match_types[$slug])) {
        $match_types[$slug] = $type;
    }
}

ksort($match_types);

function zifa_get_fixture_ids_by_type($type, $limit, $today, $direction)
{
    $meta_status = [
        'relation' => 'OR',
        [
            'key'     => 'fixture_match_status',
            'value'   => 'fixture',
            'compare' => '=',
        ],
        [
            'key'     => '_fixture_match_status',
            'value'   => 'fixture',
            'compare' => '=',
        ],
    ];

    $date_compare = ($direction === 'future') ? '>=' : '<=';

    return get_posts([
        'post_type'      => 'fixtures-results',
        'posts_per_page' => $limit,
        'post_status'    => 'publish',
        'meta_query'     => [
            'relation' => 'AND',
            [
                'relation' => 'OR',
                [
                    'key'     => 'fixture_match_type',
                    'value'   => $type,
                    'compare' => '=',
                ],
                [
                    'key'     => '_fixture_match_type',
                    'value'   => $type,
                    'compare' => '=',
                ],
            ],
            [
                'relation' => 'OR',
                [
                    'key'     => 'fixture_date',
                    'value'   => $today,
                    'compare' => $date_compare,
                    'type'    => 'DATE',
                ],
                [
                    'key'     => '_fixture_date',
                    'value'   => $today,
                    'compare' => $date_compare,
                    'type'    => 'DATE',
                ],
            ],
            $meta_status,
        ],
        'meta_key'       => '_fixture_date',
        'orderby'        => 'meta_value',
        'order'          => ($direction === 'future') ? 'ASC' : 'DESC',
        'fields'         => 'ids',
    ]);
}

function zifa_get_result_ids_by_type($type, $limit)
{
    return get_posts([
        'post_type'      => 'fixtures-results',
        'posts_per_page' => $limit,
        'post_status'    => 'publish',
        'meta_query'     => [
            'relation' => 'AND',
            [
                'relation' => 'OR',
                [
                    'key'     => 'fixture_match_type',
                    'value'   => $type,
                    'compare' => '=',
                ],
                [
                    'key'     => '_fixture_match_type',
                    'value'   => $type,
                    'compare' => '=',
                ],
            ],
            [
                'relation' => 'OR',
                [
                    'key'     => '_fixture_match_status',
                    'value'   => 'result',
                    'compare' => '=',
                ],
                [
                    'key'     => 'fixture_match_status',
                    'value'   => 'result',
                    'compare' => '=',
                ],
            ],
        ],
        'meta_key'       => '_fixture_date',
        'orderby'        => 'meta_value',
        'order'          => 'DESC',
        'fields'         => 'ids',
    ]);
}

function zifa_render_match_rows($ids, $countries, $is_result = false, $layout = 'list', $full_width = false)
{
    if (empty($ids)) {
        return;
    }

    $is_tile = ($layout === 'tile');
    if ($is_tile) {
        echo '<div class="row g-3">';
    }
    $col_class = $full_width ? 'col-12' : 'col-12 col-md-6';
    foreach ($ids as $post_id) {
        $raw_date = function_exists('carbon_get_post_meta') ? carbon_get_post_meta($post_id, 'fixture_date') : '';
        $ts = $raw_date ? strtotime($raw_date) : false;
        $date_human = $ts ? date('F j, Y', $ts) : 'Date TBA';

        $home_code = function_exists('carbon_get_post_meta') ? carbon_get_post_meta($post_id, 'fixture_country_home') : '';
        $away_code = function_exists('carbon_get_post_meta') ? carbon_get_post_meta($post_id, 'fixture_country_away') : '';

        $home_name = $home_code ? ($countries[$home_code] ?? 'Home Team') : 'Home';
        $away_name = $away_code ? ($countries[$away_code] ?? 'Away Team') : 'Away';

        $kickoff = function_exists('carbon_get_post_meta') ? carbon_get_post_meta($post_id, 'fixture_time') : '';
        $kickoff = $kickoff !== '' ? $kickoff : 'TBD';

        $home_score = function_exists('carbon_get_post_meta') ? carbon_get_post_meta($post_id, 'fixture_country_home_score') : '';
        $away_score = function_exists('carbon_get_post_meta') ? carbon_get_post_meta($post_id, 'fixture_country_away_score') : '';
        $show_score = (is_numeric($home_score) && is_numeric($away_score));
        $score_text = $show_score ? ($home_score . ' - ' . $away_score) : '-';

        $match_type = function_exists('carbon_get_post_meta') ? trim((string) carbon_get_post_meta($post_id, 'fixture_match_type')) : '';

        $stadium = function_exists('carbon_get_post_meta') ? trim((string) carbon_get_post_meta($post_id, 'fixture_stadium')) : '';
        $group_number = function_exists('carbon_get_post_meta') ? trim((string) carbon_get_post_meta($post_id, 'fixture_group_number')) : '';

        $permalink = get_permalink($post_id);
?>
        <?php if ($is_tile) : ?>
            <div class="<?php echo esc_attr($col_class); ?>">
        <?php endif; ?>
            <a class="hc-mini-row<?php echo $is_tile ? ' hc-mini-row--tile' : ''; ?>" href="<?php echo esc_url($permalink); ?>">
                <div class="hc-mini-row__side">
                    <?php if ($home_code): ?>
                        <span class="fi fi-<?php echo esc_attr($home_code); ?> fis"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="<?php echo esc_attr($home_name); ?>"></span>
                    <?php endif; ?>
                </div>

                <div class="hc-mini-row__mid">
                    <?php if ($match_type) : ?>
                        <div class="hc-mini-row__type"><?php echo esc_html($match_type); ?></div>
                    <?php endif; ?>
                    <div class="hc-mini-row__date"><?php echo esc_html($date_human); ?></div>
                    <div class="hc-mini-row__meta"><?php echo esc_html($is_result ? $score_text : $kickoff); ?></div>
                    <div class="hc-mini-row__match"><?php echo esc_html(strtoupper($home_name . ' v ' . $away_name)); ?></div>
                    <?php if ($stadium || $group_number) : ?>
                        <div class="hc-mini-row__extras">
                            <?php if ($stadium) : ?>
                                <span class="hc-mini-row__chip"><?php echo esc_html($stadium); ?></span>
                            <?php endif; ?>
                            <?php if ($group_number) : ?>
                                <?php $group_label = preg_match('/^group\\s+/i', $group_number) ? $group_number : ('Group ' . $group_number); ?>
                                <span class="hc-mini-row__chip"><?php echo esc_html($group_label); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="hc-mini-row__side">
                    <?php if ($away_code): ?>
                        <span class="fi fi-<?php echo esc_attr($away_code); ?> fis"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="<?php echo esc_attr($away_name); ?>"></span>
                    <?php endif; ?>
                </div>
            </a>
        <?php if ($is_tile) : ?>
            </div>
        <?php endif; ?>
<?php
    }
    if ($is_tile) {
        echo '</div>';
    }
}

function zifa_get_match_card_data($post_id, $countries)
{
    $raw_date = function_exists('carbon_get_post_meta') ? carbon_get_post_meta($post_id, 'fixture_date') : '';
    if ($raw_date === '') {
        $raw_date = get_post_meta($post_id, '_fixture_date', true);
    }
    $ts = $raw_date ? strtotime($raw_date) : false;
    $date_human = $ts ? date('F j, Y', $ts) : 'Date TBA';

    $home_code = function_exists('carbon_get_post_meta') ? carbon_get_post_meta($post_id, 'fixture_country_home') : '';
    $away_code = function_exists('carbon_get_post_meta') ? carbon_get_post_meta($post_id, 'fixture_country_away') : '';

    $home_name = $home_code ? ($countries[$home_code] ?? 'Home Team') : 'Home';
    $away_name = $away_code ? ($countries[$away_code] ?? 'Away Team') : 'Away';

    $kickoff = function_exists('carbon_get_post_meta') ? carbon_get_post_meta($post_id, 'fixture_time') : '';
    $kickoff = $kickoff !== '' ? $kickoff : 'TBD';

    $kickoff_ts = 0;
    if ($raw_date) {
        $try = strtotime($raw_date . ' ' . $kickoff);
        if ($try !== false) $kickoff_ts = (int) $try;
    }

    return [
        'date_human' => $date_human,
        'home_code'  => $home_code,
        'away_code'  => $away_code,
        'home_name'  => $home_name,
        'away_name'  => $away_name,
        'kickoff'    => $kickoff,
        'kickoff_ts' => $kickoff_ts,
        'permalink'  => get_permalink($post_id),
    ];
}

function zifa_get_next_match_by_type($type, $countries, $today)
{
    $ids = zifa_get_fixture_ids_by_type($type, 1, $today, 'future');
    if (empty($ids)) return null;
    return zifa_get_match_card_data($ids[0], $countries);
}

function zifa_render_next_match_panel($next_match)
{
    ?>
    <section class="hc-mini-panel hc-countdown-panel w-100 hc-card">
        <div class="hc-mini-panel__head">
            <h3 class="hc-mini-panel__title">Next Match</h3>
        </div>

        <div class="hc-mini-panel__body">
            <?php if (!empty($next_match)) : ?>
                <div class="hc-nextmatch">
                    <div class="hc-nextmatch__match">
                        <?php echo esc_html(strtoupper($next_match['home_name'] . ' v ' . $next_match['away_name'])); ?>
                    </div>

                    <?php if (!empty($next_match['kickoff_ts']) && $next_match['kickoff_ts'] > time()) : ?>
                        <div class="hc-cd-standalone" data-countdown="<?php echo esc_attr($next_match['kickoff_ts']); ?>">
                            <div class="hc-cd-standalone__item">
                                <div class="hc-cd-standalone__num" data-cd="d">0</div>
                                <div class="hc-cd-standalone__lab">days</div>
                            </div>
                            <div class="hc-cd-standalone__item">
                                <div class="hc-cd-standalone__num" data-cd="h">00</div>
                                <div class="hc-cd-standalone__lab">hrs</div>
                            </div>
                            <div class="hc-cd-standalone__item">
                                <div class="hc-cd-standalone__num" data-cd="m">00</div>
                                <div class="hc-cd-standalone__lab">mins</div>
                            </div>
                            <div class="hc-cd-standalone__item">
                                <div class="hc-cd-standalone__num" data-cd="s">00</div>
                                <div class="hc-cd-standalone__lab">secs</div>
                            </div>
                        </div>

                        <div class="hc-nextmatch__meta">
                            <?php echo esc_html($next_match['date_human']); ?> • <?php echo esc_html($next_match['kickoff']); ?>
                        </div>
                    <?php else : ?>
                        <div class="hc-nextmatch__meta">
                            Countdown: TBD
                        </div>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div class="hc-mini-empty">No upcoming fixtures.</div>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

function zifa_render_league_table_for_type($match_type)
{
    $league_table_id = 0;

    $league_q = new WP_Query([
        'post_type'      => 'league-standings',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
        'meta_query'     => [
            'relation' => 'OR',
            [
                'key'     => 'zifa_league_title',
                'value'   => $match_type,
                'compare' => '=',
            ],
            [
                'key'     => '_zifa_league_title',
                'value'   => $match_type,
                'compare' => '=',
            ],
        ],
    ]);

    if ($league_q->have_posts()) {
        $league_q->the_post();
        $league_table_id = get_the_ID();
    }
    wp_reset_postdata();

    if (! $league_table_id || !function_exists('carbon_get_post_meta')) {
        echo '<div class="hc-mini-empty">No league table for this match type.</div>';
        return;
    }

    $lt_title = (string) carbon_get_post_meta($league_table_id, 'zifa_league_title');
    $lt_group = (string) carbon_get_post_meta($league_table_id, 'zifa_league_group');
    $lt_rows  = carbon_get_post_meta($league_table_id, 'zifa_league_table');
    $lt_heading = $lt_title ?: 'League Standings';
    if ($lt_group !== '') $lt_heading .= ' - Group ' . $lt_group;

    if (!is_array($lt_rows) || empty($lt_rows)) {
        echo '<div class="hc-mini-empty">No league table yet.</div>';
        return;
    }

    $preview_limit   = 6;
    $lt_preview_rows = array_slice($lt_rows, 0, $preview_limit);
    $has_more_rows   = count($lt_rows) > $preview_limit;
    $full_table_url  = home_url('/primary-league-standings/');
    ?>
    <section class="hc-mini-panel hc-mini-panel--table w-100 hc-card">
        <div class="hc-mini-panel__head">
            <h3 class="hc-mini-panel__title">
                <?php echo esc_html($lt_heading); ?>
            </h3>
        </div>

        <div class="hc-mini-panel__body">
            <div class="hc-standings-wrap" style="overflow-x:auto;">
                <table class="hc-standings-table" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Pos</th>
                            <th style="text-align:left;">Club</th>
                            <th>P</th>
                            <th>W</th>
                            <th>D</th>
                            <th>L</th>
                            <th>GF</th>
                            <th>GA</th>
                            <th>GD</th>
                            <th>PTS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lt_preview_rows as $i => $r) :
                            $club = isset($r['club']) ? trim((string)$r['club']) : '';
                            if ($club === '') continue;

                            $p  = (int) ($r['played'] ?? 0);
                            $w  = (int) ($r['wins'] ?? 0);
                            $d  = (int) ($r['draws'] ?? 0);
                            $l  = (int) ($r['losses'] ?? 0);
                            $gf = (int) ($r['goals_for'] ?? 0);
                            $ga = (int) ($r['goals_against'] ?? 0);

                            $gd  = $gf - $ga;
                            $pts = (int) ($r['points'] ?? 0);

                            $notes = isset($r['notes']) ? trim((string)$r['notes']) : '';
                        ?>
                            <tr>
                                <td><?php echo esc_html($i + 1); ?></td>
                                <td style="text-align:left;">
                                    <?php echo esc_html($club); ?>
                                    <?php if ($notes !== '') : ?>
                                        <div style="font-size:12px; opacity:.75;">
                                            <?php echo esc_html($notes); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($p); ?></td>
                                <td><?php echo esc_html($w); ?></td>
                                <td><?php echo esc_html($d); ?></td>
                                <td><?php echo esc_html($l); ?></td>
                                <td><?php echo esc_html($gf); ?></td>
                                <td><?php echo esc_html($ga); ?></td>
                                <td><?php echo esc_html($gd); ?></td>
                                <td><strong><?php echo esc_html($pts); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>

            <?php if ($has_more_rows) : ?>
                <div class="hc-standings-footer">
                    <a class="btn btn-primary" href="<?php echo esc_url($full_table_url); ?>">
                        View full table
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </section>
    <?php
}

function zifa_render_calendar_for_type($match_type, $cal_month, $cal_year)
{
    $dt   = DateTime::createFromFormat('!Y-n', $cal_year . '-' . $cal_month);
    $prev = (clone $dt)->modify('-1 month');
    $next = (clone $dt)->modify('+1 month');
    ?>
    <section class="hc-mini-panel hc-mini-panel--calendar w-100 hc-card">
        <div class="hc-mini-panel__head">
            <h3 class="hc-mini-panel__title">Calendar</h3>
        </div>

        <div class="hc-mini-panel__body">
            <div class="hc-cal-wrap"
                data-month="<?php echo esc_attr($cal_month); ?>"
                data-year="<?php echo esc_attr($cal_year); ?>"
                data-match-type="<?php echo esc_attr($match_type); ?>">

                <?php echo function_exists('hc_render_mini_calendar') ? hc_render_mini_calendar($cal_month, $cal_year, $match_type) : ''; ?>

                <div class="hc-cal__bottomnav">
                    <a href="#"
                        class="hc-cal__nav-btn hc-cal__nav-btn--prev"
                        data-month="<?php echo esc_attr((int)$prev->format('n')); ?>"
                        data-year="<?php echo esc_attr((int)$prev->format('Y')); ?>">
                        &laquo; <?php echo esc_html($prev->format('M')); ?>
                    </a>

                    <a href="#"
                        class="hc-cal__nav-btn hc-cal__nav-btn--next"
                        data-month="<?php echo esc_attr((int)$next->format('n')); ?>"
                        data-year="<?php echo esc_attr((int)$next->format('Y')); ?>">
                        <?php echo esc_html($next->format('M')); ?> &raquo;
                    </a>
                </div>

            </div>
        </div>
    </section>
    <?php
}
?>

<section class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12">
            <?php if ($selected_type) : ?>
                <?php
                $selected_label = $match_types[$selected_type] ?? '';
                $block_slug = $selected_type;
                $fixtures_id = 'hc-fixtures-' . $block_slug;
                $results_id = 'hc-results-' . $block_slug;
                ?>
                <section class="hc-type-block mb-4"
                    data-fixture-block
                    data-match-type="<?php echo esc_attr($selected_label); ?>"
                    data-fixtures-id="<?php echo esc_attr($fixtures_id); ?>"
                    data-results-id="<?php echo esc_attr($results_id); ?>"
                    data-tile-cols="2">
                    <div class="hc-type-block__head d-flex justify-content-between align-items-center">
                        <h3 class="hc-type-block__title mb-0">
                            <?php echo esc_html($selected_label !== '' ? $selected_label : 'Match Type'); ?>
                        </h3>
                        <a class="hc-mini-link" href="<?php echo esc_url(get_permalink()); ?>">Back to all</a>
                    </div>

                    <div class="hc-type-block__body">
                        <div class="row g-3 align-items-start hc-3panels">
                            <div class="col-12 col-lg-8">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6 d-flex">
                                        <section class="hc-mini-panel hc-mini-panel--flat w-100 d-flex flex-column hc-card">
                                            <div class="hc-mini-panel__head">
                                                <h3 class="hc-mini-panel__title">Fixtures</h3>
                                            </div>
                                            <div class="hc-mini-panel__body flex-grow-1" id="<?php echo esc_attr($fixtures_id); ?>">
                                                <?php
                                                if ($selected_label !== '') {
                                                    $fixtures_ids = zifa_get_fixture_ids_by_type($selected_label, -1, $today, 'future');
                                                    zifa_render_match_rows($fixtures_ids, $countries, false, 'list');
                                                } else {
                                                    echo '<div class="hc-mini-empty">Unknown match type.</div>';
                                                }
                                                ?>
                                            </div>
                                        </section>
                                    </div>

                                    <div class="col-12 col-md-6 d-flex">
                                        <section class="hc-mini-panel hc-mini-panel--flat w-100 d-flex flex-column hc-card">
                                            <div class="hc-mini-panel__head">
                                                <h3 class="hc-mini-panel__title">Results</h3>
                                            </div>
                                            <div class="hc-mini-panel__body flex-grow-1" id="<?php echo esc_attr($results_id); ?>">
                                                <?php
                                                if ($selected_label !== '') {
                                                    $result_ids = zifa_get_result_ids_by_type($selected_label, -1);
                                                    zifa_render_match_rows($result_ids, $countries, true, 'list');
                                                }
                                                ?>
                                            </div>
                                        </section>
                                    </div>

                                    <div class="col-12">
                                        <?php if ($selected_label !== '') : ?>
                                            <?php zifa_render_league_table_for_type($selected_label); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-4">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <?php zifa_render_next_match_panel(zifa_get_next_match_by_type($selected_label, $countries, $today)); ?>
                                    </div>
                                    <div class="col-12">
                                        <?php if ($selected_label !== '') : ?>
                                            <?php zifa_render_calendar_for_type($selected_label, $cal_month, $cal_year); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php else : ?>
                <?php if (empty($match_types)) : ?>
                    <div class="hc-mini-empty">No fixtures or results found.</div>
                <?php else : ?>
                    <?php foreach ($match_types as $type_slug => $type_label) : ?>
                        <?php
                        $fixtures_ids = zifa_get_fixture_ids_by_type($type_label, 3, $today, 'future');
                        $result_ids = zifa_get_result_ids_by_type($type_label, 3);
                        if (empty($fixtures_ids) && empty($result_ids)) {
                            continue;
                        }
                        $view_all_url = add_query_arg('match_type', $type_slug, get_permalink());
                        $fixtures_id = 'hc-fixtures-' . $type_slug;
                        $results_id = 'hc-results-' . $type_slug;
                        $next_match = zifa_get_next_match_by_type($type_label, $countries, $today);
                        ?>
                <section class="hc-type-block mb-4"
                    data-fixture-block
                    data-match-type="<?php echo esc_attr($type_label); ?>"
                    data-fixtures-id="<?php echo esc_attr($fixtures_id); ?>"
                    data-results-id="<?php echo esc_attr($results_id); ?>"
                    data-tile-cols="2">
                    <div class="hc-type-block__head d-flex justify-content-between align-items-center">
                        <h3 class="hc-type-block__title mb-0"><?php echo esc_html($type_label); ?></h3>
                        <a class="hc-mini-link" href="<?php echo esc_url($view_all_url); ?>">View all</a>
                    </div>

                    <div class="hc-type-block__body">
                        <div class="row g-3 align-items-start hc-3panels">
                                    <div class="col-12 col-lg-8">
                                        <div class="row g-3">
                                            <?php if (!empty($fixtures_ids)) : ?>
                                                <div class="col-12 col-md-6 d-flex">
                                                    <section class="hc-mini-panel hc-mini-panel--flat w-100 d-flex flex-column hc-card">
                                                        <div class="hc-mini-panel__head">
                                                            <h3 class="hc-mini-panel__title">Fixtures</h3>
                                                        </div>
                                                        <div class="hc-mini-panel__body flex-grow-1" id="<?php echo esc_attr($fixtures_id); ?>">
                                                            <?php zifa_render_match_rows($fixtures_ids, $countries, false, 'list'); ?>
                                                        </div>
                                                    </section>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($result_ids)) : ?>
                                                <div class="col-12 col-md-6 d-flex">
                                                    <section class="hc-mini-panel hc-mini-panel--flat w-100 d-flex flex-column hc-card">
                                                        <div class="hc-mini-panel__head">
                                                            <h3 class="hc-mini-panel__title">Results</h3>
                                                        </div>
                                                        <div class="hc-mini-panel__body flex-grow-1" id="<?php echo esc_attr($results_id); ?>">
                                                            <?php zifa_render_match_rows($result_ids, $countries, true, 'list'); ?>
                                                        </div>
                                                    </section>
                                                </div>
                                            <?php endif; ?>
                                            <div class="col-12">
                                                <?php zifa_render_league_table_for_type($type_label); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <?php zifa_render_next_match_panel($next_match); ?>
                                            </div>
                                            <div class="col-12">
                                                <?php zifa_render_calendar_for_type($type_label, $cal_month, $cal_year); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
    (function() {
        // ===== Countdown =====
        function pad(n) {
            return String(n).padStart(2, '0');
        }

        function tick(el) {
            const target = parseInt(el.getAttribute('data-countdown'), 10);
            if (!target) return;

            const now = Math.floor(Date.now() / 1000);
            let diff = target - now;

            if (diff <= 0) {
                el.innerHTML = '<div class="hc-nextmatch__meta">Kick-off now</div>';
                return;
            }

            const d = Math.floor(diff / 86400);
            diff -= d * 86400;
            const h = Math.floor(diff / 3600);
            diff -= h * 3600;
            const m = Math.floor(diff / 60);
            const s = diff - m * 60;

            el.querySelector('[data-cd="d"]').textContent = d;
            el.querySelector('[data-cd="h"]').textContent = pad(h);
            el.querySelector('[data-cd="m"]').textContent = pad(m);
            el.querySelector('[data-cd="s"]').textContent = pad(s);
        }

        document.querySelectorAll('.hc-cd-standalone[data-countdown]').forEach(el => {
            tick(el);
            setInterval(() => tick(el), 1000);
        });

        const ajaxUrl = "<?php echo esc_js(admin_url('admin-ajax.php')); ?>";
        const nonce = "<?php echo esc_js($nonce); ?>";

        function monthLabel(m, y) {
            const d = new Date(y, m - 1, 1);
            return d.toLocaleString('en-US', {
                month: 'short'
            });
        }

        function buildBottomNavHTML(month, year) {
            const prevMonth = (month === 1) ? 12 : (month - 1);
            const prevYear = (month === 1) ? (year - 1) : year;

            const nextMonth = (month === 12) ? 1 : (month + 1);
            const nextYear = (month === 12) ? (year + 1) : year;

            return `
          <div class="hc-cal__bottomnav">
            <a href="#" class="hc-cal__nav-btn hc-cal__nav-btn--prev" data-month="${prevMonth}" data-year="${prevYear}">
              &laquo; ${monthLabel(prevMonth, prevYear)}
            </a>
            <a href="#" class="hc-cal__nav-btn hc-cal__nav-btn--next" data-month="${nextMonth}" data-year="${nextYear}">
              ${monthLabel(nextMonth, nextYear)} &raquo;
            </a>
          </div>
        `;
        }

        function ensureBottomNav(wrap, month, year) {
            const existing = wrap.querySelector('.hc-cal__bottomnav');
            if (existing) existing.outerHTML = buildBottomNavHTML(month, year);
            else wrap.insertAdjacentHTML('beforeend', buildBottomNavHTML(month, year));
        }

        async function loadCalendar(wrap, month, year) {
            if (!wrap) return;

            const matchType = wrap.dataset.matchType || '';
            const form = new FormData();
            form.append('action', 'hc_calendar');
            form.append('month', month);
            form.append('year', year);
            form.append('nonce', nonce);
            if (matchType) form.append('match_type', matchType);

            wrap.classList.add('is-loading');

            try {
                const res = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: form
                });
                const html = await res.text();

                wrap.innerHTML = html;
                wrap.dataset.month = String(month);
                wrap.dataset.year = String(year);
                ensureBottomNav(wrap, parseInt(month, 10), parseInt(year, 10));
            } catch (err) {
                console.error(err);
            } finally {
                wrap.classList.remove('is-loading');
            }
        }

        async function filterByDate(block, date) {
            const fixturesId = block.dataset.fixturesId || '';
            const resultsId = block.dataset.resultsId || '';
            const matchType = block.dataset.matchType || '';
            const layout = block.dataset.layout || 'list';

            const fixturesPanel = document.getElementById(fixturesId);
            const resultsPanel = document.getElementById(resultsId);
            if (!fixturesPanel || !resultsPanel) return;

            const prevFixtures = fixturesPanel.innerHTML;
            const prevResults = resultsPanel.innerHTML;

            fixturesPanel.classList.add('is-loading');
            resultsPanel.classList.add('is-loading');

            fixturesPanel.innerHTML = '<div class="hc-loading"></div>';
            resultsPanel.innerHTML = '<div class="hc-loading"></div>';

            const form = new FormData();
            form.append('action', 'hc_filter_by_date');
            form.append('nonce', nonce);
            form.append('date', date);
            form.append('layout', layout);
            if (matchType) form.append('match_type', matchType);

            try {
                const res = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: form
                });
                const json = await res.json();

                if (!json || !json.success) {
                    fixturesPanel.innerHTML = prevFixtures;
                    resultsPanel.innerHTML = prevResults;
                    console.error(json);
                    return;
                }

                fixturesPanel.innerHTML = json.data.fixtures_html;
                resultsPanel.innerHTML = json.data.results_html;

                if (window.bootstrap && bootstrap.Tooltip) {
                    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                        try {
                            new bootstrap.Tooltip(el);
                        } catch (e) {}
                    });
                }
            } catch (err) {
                fixturesPanel.innerHTML = prevFixtures;
                resultsPanel.innerHTML = prevResults;
                console.error(err);
            } finally {
                fixturesPanel.classList.remove('is-loading');
                resultsPanel.classList.remove('is-loading');
            }
        }

        document.addEventListener('click', function(e) {
            const nav = e.target.closest('.hc-cal__nav-btn');
            if (nav) {
                const wrap = nav.closest('.hc-cal-wrap');
                if (!wrap) return;
                e.preventDefault();
                const month = parseInt(nav.getAttribute('data-month'), 10);
                const year = parseInt(nav.getAttribute('data-year'), 10);
                if (!isNaN(month) && !isNaN(year)) loadCalendar(wrap, month, year);
                return;
            }

            const cell = e.target.closest('[data-date]');
            if (cell) {
                const block = cell.closest('[data-fixture-block]');
                if (!block) return;
                e.preventDefault();
                const date = cell.getAttribute('data-date');
                if (date) filterByDate(block, date);
            }
        });

        document.querySelectorAll('.hc-cal-wrap').forEach(wrap => {
            const m = parseInt(wrap.dataset.month, 10);
            const y = parseInt(wrap.dataset.year, 10);
            if (!isNaN(m) && !isNaN(y)) ensureBottomNav(wrap, m, y);
        });
    })();
</script>

 </div>
<?php get_footer(); ?>
