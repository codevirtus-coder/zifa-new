<?php

/**
 * Template Name: League Standings
 * Template Post Type: page
 */
get_header();
?>

<?php get_template_part('templates/banner'); ?>

<?php
$today     = wp_date('Y-m-d');
$countries = function_exists('get_countries_from_json') ? get_countries_from_json() : [];

// Calendar initial month/year + nonce (kept for consistency if you use AJAX elsewhere)
$cal_month = (int) wp_date('n');
$cal_year  = (int) wp_date('Y');
$nonce     = wp_create_nonce('hc_calendar_nonce');

/**
 * ============================
 * UPCOMING FIXTURES (fixture)
 * ============================
 */
$fixture_ids = get_posts([
    'post_type'      => 'fixtures-results',
    'posts_per_page' => 30,
    'post_status'    => 'publish',
    'meta_query'     => [
        'relation' => 'AND',
        [
            'key'     => 'fixture_match_status',
            'value'   => 'fixture',
            'compare' => '=',
        ],
        [
            'key'     => 'fixture_date',
            'value'   => $today,
            'compare' => '>=',
            'type'    => 'DATE',
        ],
    ],
    'fields' => 'ids',
]);

$fixtures = [];
foreach ($fixture_ids as $post_id) {
    $raw_date = carbon_get_post_meta($post_id, 'fixture_date') ?: '';
    $ts_date  = $raw_date ? strtotime($raw_date) : 0;

    $date_human = 'Date TBA';
    if ($ts_date) $date_human = date('F j, Y', $ts_date);

    $home_code = carbon_get_post_meta($post_id, 'fixture_country_home') ?: '';
    $away_code = carbon_get_post_meta($post_id, 'fixture_country_away') ?: '';
    $kickoff   = carbon_get_post_meta($post_id, 'fixture_time') ?: 'TBD';

    $home_name = $home_code ? ($countries[$home_code] ?? 'Home Team') : 'Home';
    $away_name = $away_code ? ($countries[$away_code] ?? 'Away Team') : 'Away';

    $kickoff_ts = 0;
    if ($raw_date) {
        $try = strtotime($raw_date . ' ' . $kickoff);
        if ($try !== false) $kickoff_ts = (int)$try;
    }

    $fixtures[] = [
        'ID'         => $post_id,
        'timestamp'  => (int)($ts_date ?: 0),
        'date_human' => $date_human,
        'home_name'  => $home_name,
        'away_name'  => $away_name,
        'kickoff'    => $kickoff,
        'kickoff_ts' => $kickoff_ts,
        'permalink'  => get_permalink($post_id),
    ];
}

// earliest first; missing dates last
usort($fixtures, function ($a, $b) {
    $ta = (int)$a['timestamp'];
    $tb = (int)$b['timestamp'];

    if ($ta === 0 && $tb > 0) return 1;
    if ($tb === 0 && $ta > 0) return -1;
    return $ta <=> $tb;
});

$next_match = !empty($fixtures) ? $fixtures[0] : null;

/**
 * ============================
 * RESULTS (result) newest first
 * ============================
 */
$result_ids = get_posts([
    'post_type'      => 'fixtures-results',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => [
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
    'fields' => 'ids',
]);

$results = [];
foreach ($result_ids as $post_id) {
    $raw_date  = carbon_get_post_meta($post_id, 'fixture_date') ?: '';
    $timestamp = $raw_date ? (int)strtotime($raw_date) : 0;
    $date_human = $timestamp ? date('F j, Y', $timestamp) : 'Date TBA';

    $home = carbon_get_post_meta($post_id, 'fixture_country_home') ?: '';
    $away = carbon_get_post_meta($post_id, 'fixture_country_away') ?: '';

    $home_name = $home ? ($countries[$home] ?? 'Home Team') : 'Home';
    $away_name = $away ? ($countries[$away] ?? 'Away Team') : 'Away';

    $home_score = carbon_get_post_meta($post_id, 'fixture_country_home_score');
    $away_score = carbon_get_post_meta($post_id, 'fixture_country_away_score');

    $show_score = is_numeric($home_score) && is_numeric($away_score);
    $score_text = $show_score ? ($home_score . ' - ' . $away_score) : '–';

    $results[] = [
        'timestamp'  => $timestamp,
        'date_human' => $date_human,
        'home_name'  => $home_name,
        'away_name'  => $away_name,
        'middle'     => $score_text,
        'permalink'  => get_permalink($post_id),
    ];
}

usort($results, function ($a, $b) {
    return ((int)$b['timestamp']) <=> ((int)$a['timestamp']);
});

$latest_result = !empty($results) ? $results[0] : null;

/**
 * ============================
 * LEAGUE TABLE (latest post)
 * ============================
 */
$league_table_id = 0;
$league_q = new WP_Query([
    'post_type'      => 'league-standings',
    'post_status'    => 'publish',
    'posts_per_page' => 1,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'no_found_rows'  => true,
]);

if ($league_q->have_posts()) {
    $league_q->the_post();
    $league_table_id = get_the_ID();
}
wp_reset_postdata();

$lt_title = 'League Standings';
$lt_rows  = [];
if ($league_table_id && function_exists('carbon_get_post_meta')) {
    $lt_title = (string) carbon_get_post_meta($league_table_id, 'zifa_league_title') ?: $lt_title;
    $lt_rows  = carbon_get_post_meta($league_table_id, 'zifa_league_table');
}
?>

<div class="container my-4">
    <div class="row g-3">

        <!-- LEFT: FULL TABLE -->
        <div class="col-12 col-lg-8">
            <section class="hc-mini-panel hc-mini-panel--table hc-card w-100">
                <div class="hc-mini-panel__head">
                    <h3 class="hc-mini-panel__title"><?php echo esc_html($lt_title); ?></h3>
                </div>

                <div class="hc-mini-panel__body">
                    <?php if (is_array($lt_rows) && !empty($lt_rows)) : ?>
                        <div class="hc-standings-wrap">
                            <table class="hc-standings-table">
                                <thead>
                                    <tr>
                                        <th>Pos</th>
                                        <th style="text-align:left;">Club</th>
                                        <th>P</th>
                                        <th>W</th>
                                        <th>D</th>
                                        <th>L</th>
                                        <th>F</th>
                                        <th>A</th>
                                        <th>GD</th>
                                        <th>Pts</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lt_rows as $i => $r) :
                                        $club = trim((string)($r['club'] ?? ''));
                                        if ($club === '') continue;

                                        $p  = (int) ($r['played'] ?? 0);
                                        $w  = (int) ($r['wins'] ?? 0);
                                        $d  = (int) ($r['draws'] ?? 0);
                                        $l  = (int) ($r['losses'] ?? 0);
                                        $gf = (int) ($r['goals_for'] ?? 0);
                                        $ga = (int) ($r['goals_against'] ?? 0);

                                        $gd  = $gf - $ga;
                                        $pts = ($w * 3) + $d;

                                        $notes = trim((string)($r['notes'] ?? ''));
                                    ?>
                                        <tr>
                                            <td><?php echo esc_html($i + 1); ?></td>
                                            <td style="text-align:left;">
                                                <?php echo esc_html($club); ?>
                                                <?php if ($notes !== '') : ?>
                                                    <div style="font-size:12px; opacity:.75;"><?php echo esc_html($notes); ?></div>
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
                    <?php else : ?>
                        <div class="hc-mini-empty">No league table yet.</div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- RIGHT: LATEST RESULT (TOP) + NEXT MATCH + CALENDAR -->
        <div class="col-12 col-lg-4">

            <!-- LATEST RESULT (ONE, TOP) -->
            <section class="hc-mini-panel hc-card w-100">
                <div class="hc-mini-panel__head">
                    <h3 class="hc-mini-panel__title">Latest Result</h3>
                </div>
                <div class="hc-mini-panel__body">
                    <?php if (!empty($latest_result)) : ?>
                        <a class="hc-mini-row" href="<?php echo esc_url($latest_result['permalink']); ?>" style="margin-bottom:0;">
                            <div class="hc-mini-row__mid" style="grid-column:1 / -1;">
                                <div class="hc-mini-row__date"><?php echo esc_html($latest_result['date_human']); ?></div>
                                <div class="hc-mini-row__meta"><?php echo esc_html($latest_result['middle']); ?></div>
                                <div class="hc-mini-row__match"><?php echo esc_html(strtoupper($latest_result['home_name'] . ' v ' . $latest_result['away_name'])); ?></div>
                            </div>
                        </a>
                    <?php else : ?>
                        <div class="hc-mini-empty">No results yet.</div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- NEXT MATCH -->
            <section class="hc-mini-panel hc-countdown-panel hc-card w-100 mt-3">
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
                                <div class="hc-nextmatch__meta">Countdown: TBD</div>
                            <?php endif; ?>
                        </div>
                    <?php else : ?>
                        <div class="hc-mini-empty">No upcoming fixtures.</div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- CALENDAR -->
            <section class="hc-mini-panel hc-mini-panel--calendar hc-card w-100 mt-3">
                <div class="hc-mini-panel__head">
                    <h3 class="hc-mini-panel__title">Calendar</h3>
                </div>

                <div class="hc-mini-panel__body">
                    <div class="hc-cal-wrap"
                        data-month="<?php echo esc_attr($cal_month); ?>"
                        data-year="<?php echo esc_attr($cal_year); ?>">
                        <?php echo function_exists('hc_render_mini_calendar') ? hc_render_mini_calendar($cal_month, $cal_year) : ''; ?>
                    </div>
                </div>
            </section>

        </div>

    </div>
</div>

<script>
    (function() {
        // ===== Countdown (standalone, only one) =====
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

            const dEl = el.querySelector('[data-cd="d"]');
            const hEl = el.querySelector('[data-cd="h"]');
            const mEl = el.querySelector('[data-cd="m"]');
            const sEl = el.querySelector('[data-cd="s"]');

            if (dEl) dEl.textContent = d;
            if (hEl) hEl.textContent = pad(h);
            if (mEl) mEl.textContent = pad(m);
            if (sEl) sEl.textContent = pad(s);
        }

        const cd = document.querySelector('.hc-cd-standalone[data-countdown]');
        if (cd) {
            tick(cd);
            setInterval(() => tick(cd), 1000);
        }
    })();
</script>

<?php get_footer(); ?>