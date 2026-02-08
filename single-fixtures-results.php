<?php get_header(); ?>


<?php get_template_part('templates/banner'); ?>


<div class="container mt-5 mb-5">
    <a href="<?php echo esc_url(home_url('/actionzone')); ?>" class="btn btn-primary mb-4">
        Back to Action Zone
    </a>

    <?php if (have_posts()) : the_post(); ?>

        <?php
        $home_code = carbon_get_post_meta(get_the_ID(), 'fixture_country_home');
        $away_code = carbon_get_post_meta(get_the_ID(), 'fixture_country_away');
        $home_name = $home_code ? (get_countries_from_json()[$home_code] ?? 'Home Team') : 'Home';
        $away_name = $away_code ? (get_countries_from_json()[$away_code] ?? 'Away Team') : 'Away';

        $home_score = carbon_get_post_meta(get_the_ID(), 'fixture_country_home_score');
        $away_score = carbon_get_post_meta(get_the_ID(), 'fixture_country_away_score');
        $match_status = carbon_get_post_meta(get_the_ID(), 'fixture_match_status');
        $show_score = is_numeric($home_score) && is_numeric($away_score);
        ?>

        <!-- Match Header -->
        <div class="card header mb-4">
            <div class="card-body text-center">
                <h3 class="card-title">
                    <?php echo esc_html($home_name); ?> vs <?php echo esc_html($away_name); ?>
                </h3>
                <p>
                    <strong><?php echo esc_html(carbon_get_post_meta(get_the_ID(), 'fixture_match_type')); ?></strong>
                    <br>
                    <?php echo esc_html(carbon_get_post_meta(get_the_ID(), 'fixture_stadium')); ?>
                    <br>
                    <small>
                        <?php
                        $raw_date = carbon_get_post_meta(get_the_ID(), 'fixture_date');
                        if ($raw_date && DateTime::createFromFormat('Y-m-d', $raw_date)) {
                            echo date('D j M Y', strtotime($raw_date));
                        } else {
                            echo 'Date TBA';
                        }
                        ?>
                        at <?php echo esc_html(carbon_get_post_meta(get_the_ID(), 'fixture_time') ?: 'TBD'); ?>
                    </small>
                </p>
            </div>
        </div>

        <!-- Scoreboard -->
        <?php if ($match_status === 'result') : ?>
            <div class="card score mb-4">
                <div class="card-body text-center">
                    <div class="row align-items-center">

                        <h4>Final Score</h4>

                        <!-- Home Team -->
                        <div class="col-5 text-end">
                            <?php if ($home_code): ?>
                                <span class="fi fi-<?php echo esc_attr($home_code); ?> fis me-2"
                                    data-bs-toggle="tooltip"
                                    title="<?php echo esc_attr($home_name); ?>"></span>
                            <?php endif; ?>
                        </div>

                        <div class="col-2">
                            <span class="fs-4 fw-bold">
                                <?php echo $show_score ? "$home_score - $away_score" : '–'; ?>
                            </span>
                            <?php if ($show_score): ?><br><small>FT</small><?php endif; ?>
                        </div>

                        <!-- Away Team -->
                        <div class="col-5 text-start">
                            <?php if ($away_code): ?>
                                <span class="fi fi-<?php echo esc_attr($away_code); ?> fis me-2"
                                    data-bs-toggle="tooltip"
                                    title="<?php echo esc_attr($away_name); ?>"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <div class="card mb-4 text-center">
                <div class="card-body">
                    <h3 class="mb-0">
                        <strong>Kick-off Time:</strong>
                        <?php echo esc_html(carbon_get_post_meta(get_the_ID(), 'fixture_time') ?: 'TBD'); ?>
                    </h3>
                </div>
            </div>
        <?php endif; ?>


        <!-- Match Statistics -->
        <?php
        $stats = carbon_get_post_meta(get_the_ID(), 'match_stats');
        if (!empty($stats) && $match_status === 'result') :
            $stat = $stats[0];
        ?>
            <h4 class="mt-5">Match Statistics</h4>
            <div class="card stats mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-info text-white border rounded">
                                <p><strong>Possession</strong></p>
                                <span>
                                    <?php echo esc_attr($home_name); ?> - <?php echo esc_html($stat['possession_home'] ?? '-'); ?>%
                                </span>

                                <br>

                                <span>
                                    <?php echo esc_attr($away_name); ?> - <?php echo esc_html($stat['possession_away'] ?? '-'); ?>%
                                </span>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-primary text-white border rounded">
                                <p><strong>Shots on Target</strong></p>
                                <span>
                                    <?php echo esc_attr($home_name); ?> - <?php echo esc_html($stat['shots_on_target_home'] ?? '-'); ?>
                                </span>

                                <br>

                                <span>
                                    <?php echo esc_attr($away_name); ?> - <?php echo esc_html($stat['shots_on_target_away'] ?? '-'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-secondary text-black border rounded">
                                <p><strong>Yellow Cards</strong></p>
                                <span>
                                    <?php echo esc_attr($home_name); ?> - <?php echo esc_html($stat['yellow_cards_home'] ?? '-'); ?>
                                </span>

                                <br>

                                <span>
                                    <?php echo esc_attr($away_name); ?> - <?php echo esc_html($stat['yellow_cards_away'] ?? '-'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-danger text-white border rounded">
                                <p><strong>Red Cards</strong></p>
                                <span>
                                    <?php echo esc_attr($home_name); ?> - <?php echo esc_html($stat['red_cards_home'] ?? '-'); ?>
                                </span>

                                <br>

                                <span>
                                    <?php echo esc_attr($away_name); ?> - <?php echo esc_html($stat['red_cards_away'] ?? '-'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>


        <?php if ($match_status === 'result') : ?>
            <!-- Match Details & Goals -->
            <h4 class="mt-5">Match Details & Goals</h4>
            <div class="card stats mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <!-- Match Details -->
                            <h5>Match Details</h5>
                            <?php if ($match_status === 'result') : ?>
                                <ul class="list-group">
                                    <li class="list-group-item list-group-item-light">
                                        <strong>Duration:</strong>
                                        <br>
                                        <?php echo esc_html(carbon_get_post_meta(get_the_ID(), 'match_duration') ?: '90 minutes'); ?>
                                    </li>
                                    <li class="list-group-item list-group-item-light">
                                        <strong>Referee:</strong>
                                        <br>
                                        <?php echo esc_html(carbon_get_post_meta(get_the_ID(), 'match_referee') ?: 'Not specified'); ?>
                                    </li>
                                    <li class="list-group-item list-group-item-light">
                                        <strong>Attendance:</strong>
                                        <br>
                                        <?php echo number_format(carbon_get_post_meta(get_the_ID(), 'match_attendance') ?: 0); ?>
                                    </li>
                                </ul>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <!-- Goals Scored -->
                            <h5>Goals Scored</h5>
                            <?php $goals = carbon_get_post_meta(get_the_ID(), 'match_goals');
                            if (!empty($goals) && $match_status === 'result') : ?>
                                <ul class="list-group">
                                    <?php foreach ($goals as $goal) : ?>
                                        <li class="list-group-item list-group-item-light">

                                            <strong><?php echo esc_html($goal['minute']); ?> min</strong>
                                            <br>

                                            <?php echo esc_html($goal['player']); ?> -

                                            <?php if ($goal['team'] === 'home'): ?>
                                                <!-- <span class="fi fi-<?php echo esc_attr($home_code); ?> fis"></span> -->
                                                <?php echo esc_html($home_name); ?>
                                            <?php else: ?>
                                                <!-- <span class="fi fi-<?php echo esc_attr($away_code); ?> fis"></span> -->
                                                <?php echo esc_html($away_name); ?>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <div class="alert alert-warning">Match not found.</div>
    <?php endif; ?>
</div>


<?php get_footer(); ?>