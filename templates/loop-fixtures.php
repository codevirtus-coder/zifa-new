<?php
$today     = wp_date('Y-m-d');
$countries = function_exists('get_countries_from_json') ? get_countries_from_json() : [];

/**
 * ============================
 * UPCOMING FIXTURES (fixture)
 * - show ONLY fixtures today or future
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

    // Build kickoff timestamp for countdown (best-effort)
    $kickoff_ts = 0;
    if ($raw_date) {
        $try = strtotime($raw_date . ' ' . $kickoff);
        if ($try !== false) $kickoff_ts = (int)$try;
    }

    $fixtures[] = [
        'ID'         => $post_id,
        'timestamp'  => (int)($ts_date ?: 0),
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

// sort earliest first; missing dates last
usort($fixtures, function ($a, $b) {
    $ta = (int)$a['timestamp'];
    $tb = (int)$b['timestamp'];

    if ($ta === 0 && $tb > 0) return 1;
    if ($tb === 0 && $ta > 0) return -1;

    return $ta <=> $tb;
});

$fixtures_to_show = array_slice($fixtures, 0, 3);

// next match for countdown (only one)
$next_match = !empty($fixtures) ? $fixtures[0] : null;


/**
 * ==============================================
 * RESULTS (result)
 * ==============================================
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

if (!empty($result_ids)) {
    foreach ($result_ids as $post_id) {

        $home = carbon_get_post_meta($post_id, 'fixture_country_home') ?: '';
        $away = carbon_get_post_meta($post_id, 'fixture_country_away') ?: '';

        if ($home === '' && $away === '') $min_code = '';
        elseif ($home === '') $min_code = $away;
        elseif ($away === '') $min_code = $home;
        else $min_code = (strcmp($home, $away) <= 0) ? $home : $away;

        $raw_date = carbon_get_post_meta($post_id, 'fixture_date') ?: '';
        $timestamp = 0;

        if ($raw_date) {
            $ts = strtotime($raw_date);
            if ($ts !== false) $timestamp = (int)$ts;
        }

        $date_human = $timestamp ? date('F j, Y', $timestamp) : 'Date TBA';

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

$results_to_show = array_slice($results, 0, 3);

// Calendar initial month/year + nonce
$cal_month = (int) wp_date('n');
$cal_year  = (int) wp_date('Y');
$nonce     = wp_create_nonce('hc_calendar_nonce');
?>

<div class="row g-3 align-items-start hc-3panels">

    <!-- LEFT SIDE -->
    <div class="col-12 col-lg-8">
        <div class="row g-3">

            <!-- FIXTURES -->
            <div class="col-12 col-md-6 d-flex">
                <section class="hc-mini-panel w-100 d-flex flex-column hc-card">
                    <div class="hc-mini-panel__head">
                        <h3 class="hc-mini-panel__title">Fixtures</h3>
                    </div>

                    <div class="hc-mini-panel__body flex-grow-1" id="hc-fixtures-panel">
                        <?php if (!empty($fixtures_to_show)) : ?>
                            <?php foreach ($fixtures_to_show as $m) : ?>
                                <a class="hc-mini-row" href="<?php echo esc_url($m['permalink']); ?>">
                                    <div class="hc-mini-row__side">
                                        <?php if ($m['home_code']) : ?>
                                            <span class="fi fi-<?php echo esc_attr($m['home_code']); ?> fis"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="<?php echo esc_attr($m['home_name']); ?>"></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="hc-mini-row__mid">
                                        <div class="hc-mini-row__date"><?php echo esc_html($m['date_human']); ?></div>
                                        <div class="hc-mini-row__meta"><?php echo esc_html($m['kickoff']); ?></div>
                                        <div class="hc-mini-row__match"><?php echo esc_html(strtoupper($m['home_name'] . ' v ' . $m['away_name'])); ?></div>
                                    </div>

                                    <div class="hc-mini-row__side">
                                        <?php if ($m['away_code']) : ?>
                                            <span class="fi fi-<?php echo esc_attr($m['away_code']); ?> fis"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="<?php echo esc_attr($m['away_name']); ?>"></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="hc-mini-empty">No upcoming fixtures.</div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <!-- RESULTS -->
            <div class="col-12 col-md-6 d-flex">
                <section class="hc-mini-panel w-100 d-flex flex-column hc-card">
                    <div class="hc-mini-panel__head">
                        <h3 class="hc-mini-panel__title">Results</h3>
                    </div>

                    <div class="hc-mini-panel__body flex-grow-1" id="hc-results-panel">
                        <?php if (!empty($results_to_show)) : ?>
                            <?php foreach ($results_to_show as $m) : ?>
                                <a class="hc-mini-row" href="<?php echo esc_url($m['permalink']); ?>">
                                    <div class="hc-mini-row__side">
                                        <?php if ($m['home_code']) : ?>
                                            <span class="fi fi-<?php echo esc_attr($m['home_code']); ?> fis"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="<?php echo esc_attr($m['home_name']); ?>"></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="hc-mini-row__mid">
                                        <div class="hc-mini-row__date"><?php echo esc_html($m['date_human']); ?></div>
                                        <div class="hc-mini-row__meta"><?php echo esc_html($m['middle']); ?></div>
                                        <div class="hc-mini-row__match"><?php echo esc_html(strtoupper($m['home_name'] . ' v ' . $m['away_name'])); ?></div>
                                    </div>

                                    <div class="hc-mini-row__side">
                                        <?php if ($m['away_code']) : ?>
                                            <span class="fi fi-<?php echo esc_attr($m['away_code']); ?> fis"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="<?php echo esc_attr($m['away_name']); ?>"></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="hc-mini-empty">No results yet.</div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <!-- LEAGUE STANDINGS (unchanged) -->
            <div class="col-12">
                <?php
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

                $full_table_url = home_url('/primary-league-standings/');

                if ($league_table_id && function_exists('carbon_get_post_meta')) :
                    $lt_title = (string) carbon_get_post_meta($league_table_id, 'zifa_league_title');
                    $lt_rows  = carbon_get_post_meta($league_table_id, 'zifa_league_table');

                    if (is_array($lt_rows) && !empty($lt_rows)) :
                        $preview_limit   = 6;
                        $lt_preview_rows = array_slice($lt_rows, 0, $preview_limit);
                        $has_more_rows   = count($lt_rows) > $preview_limit;
                ?>
                        <section class="hc-mini-panel hc-mini-panel--table w-100 hc-card">
                            <div class="hc-mini-panel__head">
                                <h3 class="hc-mini-panel__title">
                                    <?php echo esc_html($lt_title ?: 'League Standings'); ?>
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
                                                <th>F</th>
                                                <th>A</th>
                                                <th>GD</th>
                                                <th>Pts</th>
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
                                                $pts = ($w * 3) + $d;

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
                    endif;
                endif;
                ?>
            </div>

        </div>
    </div>

    <!-- RIGHT SIDE -->
    <div class="col-12 col-lg-4">
        <div class="row g-3">

            <!-- NEXT MATCH -->
            <div class="col-12">
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
            </div>

            <!-- Calendar -->
            <div class="col-12">
                <section class="hc-mini-panel hc-mini-panel--calendar w-100 hc-card">
                    <div class="hc-mini-panel__head">
                        <h3 class="hc-mini-panel__title">Calendar</h3>
                    </div>

                    <div class="hc-mini-panel__body">
                        <div class="hc-cal-wrap"
                            data-month="<?php echo esc_attr($cal_month); ?>"
                            data-year="<?php echo esc_attr($cal_year); ?>">

                            <?php echo function_exists('hc_render_mini_calendar') ? hc_render_mini_calendar($cal_month, $cal_year) : ''; ?>

                            <?php

                            $dt   = DateTime::createFromFormat('!Y-n', $cal_year . '-' . $cal_month);
                            $prev = (clone $dt)->modify('-1 month');
                            $next = (clone $dt)->modify('+1 month');
                            ?>
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
            </div>

        </div>
    </div>

</div>


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

        const cd = document.querySelector('.hc-cd-standalone[data-countdown]');
        if (cd) {
            tick(cd);
            setInterval(() => tick(cd), 1000);
        }

        // ===== AJAX =====
        const ajaxUrl = "<?php echo esc_js(admin_url('admin-ajax.php')); ?>";
        const nonce = "<?php echo esc_js($nonce); ?>";
        const todayISO = "<?php echo esc_js($today); ?>";

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
                <a href="#"
                    class="hc-cal__nav-btn hc-cal__nav-btn--prev"
                    data-month="${prevMonth}"
                    data-year="${prevYear}">
                    &laquo; ${monthLabel(prevMonth, prevYear)}
                </a>

                <a href="#"
                    class="hc-cal__nav-btn hc-cal__nav-btn--next"
                    data-month="${nextMonth}"
                    data-year="${nextYear}">
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

        async function filterByDate(date) {
            const fixturesPanel = document.getElementById('hc-fixtures-panel');
            const resultsPanel = document.getElementById('hc-results-panel');
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

        // delegated clicks:
        document.addEventListener('click', function(e) {
            const nav = e.target.closest('.hc-cal__nav-btn');
            if (nav) {
                e.preventDefault();
                const month = parseInt(nav.getAttribute('data-month'), 10);
                const year = parseInt(nav.getAttribute('data-year'), 10);

                const form = new FormData();
                form.append('action', 'hc_calendar');
                form.append('month', month);
                form.append('year', year);
                form.append('nonce', nonce);

                const wrap = document.querySelector('.hc-cal-wrap');
                if (!wrap) return;

                wrap.classList.add('is-loading');

                fetch(ajaxUrl, {
                        method: 'POST',
                        body: form
                    })
                    .then(r => r.text())
                    .then(html => {
                        wrap.innerHTML = html;


                        ensureBottomNav(wrap, month, year);
                    })
                    .catch(console.error)
                    .finally(() => wrap.classList.remove('is-loading'));

                return;
            }

            const day = e.target.closest('[data-date]');
            if (day) {
                e.preventDefault();
                const date = day.getAttribute('data-date');
                if (date) filterByDate(date);
            }
        });


        const wrap0 = document.querySelector('.hc-cal-wrap');
        if (wrap0) {
            const m0 = parseInt(wrap0.dataset.month, 10);
            const y0 = parseInt(wrap0.dataset.year, 10);
            if (!isNaN(m0) && !isNaN(y0)) ensureBottomNav(wrap0, m0, y0);
        }
    })();
</script>