<?php

/**
 * Template Name: Action Zone - Fixtures Only
 * Description: Fixtures page with calendar filtering (future fixtures only)
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

<?php
$today     = wp_date('Y-m-d');
$countries = function_exists('get_countries_from_json') ? get_countries_from_json() : [];
$nonce     = wp_create_nonce('hc_calendar_nonce');

// default month/year
$cal_month = (int) wp_date('n');
$cal_year  = (int) wp_date('Y');

/**
 * Default list: show upcoming fixtures (today+future)
 */
$post_ids = get_posts([
    'post_type'      => 'fixtures-results',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => [
        'relation' => 'AND',
        [
            'key'     => 'fixture_date',
            'value'   => $today,
            'compare' => '>=',
            'type'    => 'DATE',
        ],
        [
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
        ],
    ],
    'fields' => 'ids',
]);

$fixtures = [];
foreach ($post_ids as $post_id) {
    $home = carbon_get_post_meta($post_id, 'fixture_country_home') ?: '';
    $away = carbon_get_post_meta($post_id, 'fixture_country_away') ?: '';

    $min_code = '';
    if ($home === '' && $away === '') $min_code = '';
    elseif ($home === '') $min_code = $away;
    elseif ($away === '') $min_code = $home;
    else $min_code = (strcmp($home, $away) <= 0) ? $home : $away;

    $raw_date = carbon_get_post_meta($post_id, 'fixture_date') ?: '';
    $timestamp = 0;
    $normalized_date = '';

    if ($raw_date) {
        $ts = strtotime($raw_date);
        if ($ts !== false) {
            $normalized_date = date('Y-m-d', $ts);
            $timestamp = (int)$ts;
        }
    }

    $fixtures[] = [
        'ID'        => $post_id,
        'min_code'  => $min_code,
        'raw_date'  => $normalized_date,
        'timestamp' => $timestamp,
    ];
}

// sort date ASC, tie-break min_code
usort($fixtures, function ($a, $b) {
    $dateA = $a['raw_date'];
    $dateB = $b['raw_date'];

    if ($dateA !== '' && $dateB !== '') {
        if ($dateA === $dateB) return strcmp($a['min_code'], $b['min_code']);
        return strcmp($dateA, $dateB);
    }

    if ($dateA !== '' && $dateB === '') return -1;
    if ($dateA === '' && $dateB !== '') return 1;

    return strcmp($a['min_code'], $b['min_code']);
});
?>

<section class="container mt-5 mb-5">
    <div class="row g-4">

        <!-- LEFT: FIXTURES LIST -->
        <div class="col-12 col-lg-8">
            <section class="hc-mini-panel w-100">
                <div class="hc-mini-panel__head d-flex justify-content-between align-items-center">
                    <h3 class="hc-mini-panel__title mb-0">Fixtures</h3>
                    <div id="hc-selected-date" style="font-weight:600; opacity:.85;"></div>
                </div>


                <div class="hc-mini-panel__body" id="hc-fixtures-grid">
                    <?php if (!empty($fixtures)) : ?>
                        <div class="row g-3">
                            <?php foreach ($fixtures as $item) :
                                $post_id = $item['ID'];

                                $raw_date = carbon_get_post_meta($post_id, 'fixture_date');
                                $ts = $raw_date ? strtotime($raw_date) : false;
                                $date_human = $ts ? date('F j, Y', $ts) : 'Date TBA';

                                $home_code = carbon_get_post_meta($post_id, 'fixture_country_home') ?: '';
                                $away_code = carbon_get_post_meta($post_id, 'fixture_country_away') ?: '';

                                $home_name = $home_code ? ($countries[$home_code] ?? 'Home Team') : 'Home';
                                $away_name = $away_code ? ($countries[$away_code] ?? 'Away Team') : 'Away';

                                $kickoff = carbon_get_post_meta($post_id, 'fixture_time') ?: 'TBD';
                                $permalink = get_permalink($post_id);
                            ?>
                                <div class="col-12 col-md-6">
                                    <a class="hc-mini-row hc-mini-row--tile" href="<?php echo esc_url($permalink); ?>">
                                        <div class="hc-mini-row__side">
                                            <?php if ($home_code): ?>
                                                <span class="fi fi-<?php echo esc_attr($home_code); ?> fis"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="<?php echo esc_attr($home_name); ?>"></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="hc-mini-row__mid">
                                            <div class="hc-mini-row__date"><?php echo esc_html($date_human); ?></div>
                                            <div class="hc-mini-row__meta"><?php echo esc_html($kickoff); ?></div>
                                            <div class="hc-mini-row__match"><?php echo esc_html(strtoupper($home_name . ' v ' . $away_name)); ?></div>
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
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="hc-mini-empty">No upcoming fixtures.</div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- RIGHT: CALENDAR -->
        <div class="col-12 col-lg-4">
            <section class="hc-mini-panel hc-mini-panel--calendar w-100">
                <div class="hc-mini-panel__head">
                    <h3 class="hc-mini-panel__title">Calendar</h3>
                </div>

                <div class="hc-mini-panel__body">
                    <div class="hc-cal-wrap"
                        data-month="<?php echo esc_attr($cal_month); ?>"
                        data-year="<?php echo esc_attr($cal_year); ?>">

                        <?php echo function_exists('hc_render_mini_calendar_fixtures_only')
                            ? hc_render_mini_calendar_fixtures_only($cal_month, $cal_year)
                            : ''; ?>

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
</section>


<script>
    (function() {
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

        async function loadCalendar(month, year) {
            const wrap = document.querySelector('.hc-cal-wrap');
            if (!wrap) return;

            const form = new FormData();
            form.append('action', 'hc_calendar_fixtures');
            form.append('month', month);
            form.append('year', year);
            form.append('nonce', nonce);

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

        async function filterFixturesByDate(date) {
            const grid = document.getElementById('hc-fixtures-grid');
            const label = document.getElementById('hc-selected-date');
            if (!grid) return;

            const prevHTML = grid.innerHTML;
            grid.classList.add('is-loading');
            grid.innerHTML = '<div class="hc-loading">Loading fixtures…</div>';
            if (label) label.textContent = date;

            const form = new FormData();
            form.append('action', 'hc_filter_fixtures_by_date');
            form.append('nonce', nonce);
            form.append('date', date);

            try {
                const res = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: form
                });
                const json = await res.json();

                if (!json || !json.success) {
                    grid.innerHTML = prevHTML;
                    console.error(json);
                    return;
                }

                grid.innerHTML = json.data.html;

                // re-init tooltips
                if (window.bootstrap && bootstrap.Tooltip) {
                    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                        try {
                            new bootstrap.Tooltip(el);
                        } catch (e) {}
                    });
                }
            } catch (err) {
                grid.innerHTML = prevHTML;
                console.error(err);
            } finally {
                grid.classList.remove('is-loading');
            }
        }

        // delegated clicks
        document.addEventListener('click', function(e) {
            const nav = e.target.closest('.hc-cal__nav-btn');
            if (nav) {
                e.preventDefault();
                const month = parseInt(nav.getAttribute('data-month'), 10);
                const year = parseInt(nav.getAttribute('data-year'), 10);
                if (!isNaN(month) && !isNaN(year)) loadCalendar(month, year);
                return;
            }

            const cell = e.target.closest('[data-date]');
            if (cell) {
                e.preventDefault();
                const date = cell.getAttribute('data-date');
                if (date) filterFixturesByDate(date);
            }
        });

        // initial ensure
        const wrap0 = document.querySelector('.hc-cal-wrap');
        if (wrap0) {
            const m0 = parseInt(wrap0.dataset.month, 10);
            const y0 = parseInt(wrap0.dataset.year, 10);
            if (!isNaN(m0) && !isNaN(y0)) ensureBottomNav(wrap0, m0, y0);
        }
    })();
</script>

<?php get_footer(); ?>