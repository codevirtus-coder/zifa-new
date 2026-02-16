<?php

/**
 * Template Name: League Standings - By Match Type
 * Description: Show all league tables for a specific match type (grouped).
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
$match_type = isset($_GET['match_type']) ? sanitize_text_field($_GET['match_type']) : '';
$fixtures_url = home_url('/fixtures/');

if ($match_type === '') {
    echo '<section class="container mb-5"><div class="hc-mini-empty">Select a match type to view tables.</div></section>';
    get_footer();
    return;
}

$tables = new WP_Query([
    'post_type'      => 'league-standings',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
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
?>

<?php
// Sort tables by group A, B, C... then by title
if (!empty($tables->posts)) {
    usort($tables->posts, function ($a, $b) {
        $ga = (string) get_post_meta($a->ID, 'zifa_league_group', true);
        if ($ga === '') $ga = (string) get_post_meta($a->ID, '_zifa_league_group', true);
        $gb = (string) get_post_meta($b->ID, 'zifa_league_group', true);
        if ($gb === '') $gb = (string) get_post_meta($b->ID, '_zifa_league_group', true);

        $ga = strtoupper(trim($ga));
        $gb = strtoupper(trim($gb));

        if ($ga !== $gb) {
            if ($ga === '') return 1;
            if ($gb === '') return -1;
            return strcmp($ga, $gb);
        }

        return strcmp($a->post_title, $b->post_title);
    });
}
?>

<section class="container mb-3">
    <div class="row">
        <div class="col-12 d-flex justify-content-end">
            <a class="btn btn-primary" href="<?php echo esc_url($fixtures_url); ?>">Back to fixtures</a>
        </div>
    </div>
</section>

<section class="container mb-5">
    <?php if ($tables->have_posts()) : ?>
        <?php foreach ($tables->posts as $post) : setup_postdata($post); ?>
            <?php
            $table_id = get_the_ID();
            $lt_title = (string) carbon_get_post_meta($table_id, 'zifa_league_title');
            $lt_group = (string) carbon_get_post_meta($table_id, 'zifa_league_group');
            $lt_season = (string) carbon_get_post_meta($table_id, 'zifa_league_season');
            $lt_rows  = carbon_get_post_meta($table_id, 'zifa_league_table');

            $lt_heading = $lt_title ?: 'League Standings';
            if ($lt_group !== '') $lt_heading .= ' - Group ' . $lt_group;
            ?>

            <section class="hc-mini-panel hc-mini-panel--table w-100 hc-card mb-4">
                <div class="hc-mini-panel__head">
                    <h3 class="hc-mini-panel__title">
                        <?php echo esc_html($lt_heading); ?>
                    </h3>
                </div>

                <div class="hc-mini-panel__body">
                    <?php if ($lt_season !== '') : ?>
                        <div style="margin-bottom:10px; font-weight:700; opacity:.75;">
                            <?php echo esc_html($lt_season); ?>
                        </div>
                    <?php endif; ?>

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
                                <?php if (is_array($lt_rows) && !empty($lt_rows)) : ?>
                                    <?php foreach ($lt_rows as $i => $r) :
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
                                <?php else : ?>
                                    <tr>
                                        <td colspan="10" style="text-align:center; padding:12px;">No table data.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php endforeach; wp_reset_postdata(); ?>
    <?php else : ?>
        <div class="hc-mini-empty">No tables found for this match type.</div>
    <?php endif; ?>
</section>

<?php get_footer(); ?>
