<?php

// // Check if we loading the right functions.php page
// die('This is the correct functions.php');

function zifa_website_setup()
{
    // Navigation Menu/s
    add_theme_support('menus');
    register_nav_menus(array(
        'main_menu' => ('Desktop Menu'),
        'mobile_menu' => ('Mobile Menu'),
        'external_menu' => ('External Sites Menu'),
        'footer_menu' => ('Footer Menu'),
        'copyright_menu' => ('Copyrights Menu'),
    ));

    // Post Formats
    add_theme_support('post-formats', array('aside', 'image', 'video', 'gallery', 'quote', 'link'));

    // Switch default core markup to output valid HTML5.
    add_theme_support('html5', array(
        'search-form',
        // 'comment-form',
        // 'comment-list',
        'gallery',
        'caption',
    ));

    add_theme_support('woocommerce');

    // Featured Image/s + Croping
    add_theme_support('post-thumbnails');
    add_image_size('slider-image', 1400, 510, true);

    // Custom Colors
    add_theme_support('editor-color-palette', array(
        array(
            'name'  => __('Green', 'zifaGreen'),
            'slug'  => 'zifa-green',
            'color'    => '#036F3C',
        ),
        array(
            'name'  => __('Yellow', 'zifaYellow'),
            'slug'  => 'zifa-yellow',
            'color' => '#FFD100',
        ),
        array(
            'name'  => __('Red', 'zifaRed'),
            'slug'  => 'zifa-red',
            'color' => '#ED3237',
        ),
    ));

    // uncomment code below if you need to Disable Custom Colors
    add_theme_support('disable-custom-colors');
}
add_action('after_setup_theme', 'zifa_website_setup');





/************************************
	Enqueue scripts and styles
 ************************************/
function zifa_website_scripts()
{
    wp_enqueue_style('theme-style', get_stylesheet_uri());
    wp_enqueue_script('bootstrap', get_theme_file_uri() . '/js/bootstrap.bundle.min.js', array(), '1.5.5', true);
    wp_enqueue_script('swiper', get_theme_file_uri() . '/js/swiper-bundle.min.js', array(), '1.5.5', true);
    wp_enqueue_script('app', get_theme_file_uri() . '/js/app.js', array(), '1.5.5', true);
}
add_action('wp_enqueue_scripts', 'zifa_website_scripts');





/************************************
    Disable the WP REST API
    https://developer.wordpress.org/rest-api/frequently-asked-questions/#can-i-disable-the-rest-api
 ************************************/
// add_filter( 'rest_authentication_errors', function( $result ) {
//     if ( true === $result || is_wp_error( $result ) ) {
//         return $result;
//     }

//     if ( ! is_user_logged_in() ) {
//         return new WP_Error(
//             'rest_not_logged_in',
//             __( 'You are not currently logged in.' ),
//             array( 'status' => 401 )
//         );
//     }
    
//     return $result;
// });





/************************************
    Completely Disable Comments
    https://www.wpbeginner.com/wp-tutorials/how-to-completely-disable-comments-in-wordpress
 ************************************/
add_action('admin_init', function () {
    // Redirect any user trying to access comments page
    global $pagenow;

    if ($pagenow === 'edit-comments.php') {
        wp_safe_redirect(admin_url());
        exit;
    }

    // Remove comments metabox from dashboard
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

    // Disable support for comments and trackbacks in post types
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

// Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// Remove comments page in menu
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

// Remove comments links from admin bar
add_action('init', function () {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
});





/************************************
    Image Editor Library - upload error fix
    SOURCE: www.wpbeginner.com/wp-tutorials/how-to-fix-the-http-image-upload-error-in-wordpress
 *************************************/
function zifa_website_image_editor($editors)
{
    $gd_editor = 'WP_Image_Editor_GD';
    $editors = array_diff($editors, array($gd_editor));
    array_unshift($editors, $gd_editor);
    return $editors;
}
add_filter('wp_image_editors', 'zifa_website_image_editor');






/************************************
    WordPress User Last Login Tracker
    Author: thatAfro
    Author URI: https://thatafro.netlify.app/
    
    Features:
    - Tracks user login timestamps
    - Displays last login in admin users table
    - Provides shortcode for frontend display
    - Hidden display option for specific users
 ************************************/

/**
 * Configuration Settings
 * Set HIDE_ADMIN_LOGIN to false to show actual login times for specified users
 */
define('HIDE_ADMIN_LOGIN', true); // To toggle hiding on/off use true or false


/**
 * Users to hide login data from (while still recording it)
 * Add usernames and emails as needed
 */
function get_hidden_login_users()
{
    return [
        'usernames' => ['mehluli', 'admin', 'another_user'],
        'emails'    => ['mehlulihikwa@gmail.com', 'admin@site.com']
    ];
}

/**
 * Check if user should have hidden login display
 * 
 * @param int $user_id User ID to check
 * @return bool True if login should be hidden, false otherwise
 */
function should_hide_login_display($user_id)
{
    // If hiding is disabled, always show real data
    if (!HIDE_ADMIN_LOGIN) {
        return false;
    }

    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }

    $hidden_users = get_hidden_login_users();

    // Check if username or email matches hidden users list
    return in_array($user->user_login, $hidden_users['usernames']) ||
        in_array($user->user_email, $hidden_users['emails']);
}

/**
 * Capture and store user login timestamp
 * Triggered on every successful login
 * 
 * @param string $user_login Username of logged in user
 * @param WP_User $user User object
 */
function capture_user_login_timestamp($user_login, $user)
{
    // Always save the actual login time (even for hidden users)
    update_user_meta($user->ID, 'last_login', time());
}
add_action('wp_login', 'capture_user_login_timestamp', 10, 2);

/**
 * Add Last Login column to admin users table
 * 
 * @param array $columns Existing columns array
 * @return array Modified columns array with Last Login added
 */
function add_last_login_column($columns)
{
    $columns['last_login'] = 'Last Login';
    return $columns;
}
add_filter('manage_users_columns', 'add_last_login_column');

/**
 * Display last login data in admin users table column
 * Shows "No Record" for specified users when hiding is enabled
 * 
 * @param string $output Column output
 * @param string $column_id Column identifier
 * @param int $user_id User ID
 * @return string Formatted last login display
 */
function display_last_login_column($output, $column_id, $user_id)
{
    if ($column_id !== 'last_login') {
        return $output;
    }

    // Check if this user's login should be hidden
    if (should_hide_login_display($user_id)) {
        return 'No Record';
    }

    // Get and format actual login time
    $last_login = get_user_meta($user_id, 'last_login', true);

    if (!$last_login) {
        return 'No Record';
    }

    // Format dates for display and hover tooltip
    $hover_format = 'F j, Y, g:i a';  // Full date with time
    $hover_text = date($hover_format, $last_login);
    $human_diff = human_time_diff($last_login);

    return sprintf(
        '<div title="Last login: %s">%s ago</div>',
        esc_attr($hover_text),
        esc_html($human_diff)
    );
}
add_filter('manage_users_custom_column', 'display_last_login_column', 10, 3);

/**
 * Get formatted last login time for current user
 * Used by shortcode and can be called directly
 * 
 * @param int|null $user_id Optional user ID, defaults to current author
 * @return string Human readable time difference or "No Record"
 */
function get_user_last_login_display($user_id = null)
{
    // Use current author if no user ID provided
    if ($user_id === null) {
        $user_id = get_the_author_meta('ID');
    }

    // Check if login should be hidden for this user
    if (should_hide_login_display($user_id)) {
        return 'No Record';
    }

    // Get last login timestamp
    $last_login = get_user_meta($user_id, 'last_login', true);

    if (!$last_login) {
        return 'No Record';
    }

    return human_time_diff($last_login) . ' ago';
}

/**
 * Shortcode handler for displaying last login
 * Usage: [last_login] or [last_login user_id="123"]
 * 
 * @param array $atts Shortcode attributes
 * @return string Formatted last login display
 */
function last_login_shortcode($atts)
{
    $atts = shortcode_atts([
        'user_id' => null
    ], $atts, 'last_login');

    return get_user_last_login_display($atts['user_id']);
}
add_shortcode('last_login', 'last_login_shortcode');

// Backward compatibility - keep old shortcode name
add_shortcode('that_afro_themelastlogin', 'last_login_shortcode');

// Shortcode usage:
// [last_login]                    // Current author's login
// [last_login user_id="123"]      // Specific user's login
// [that_afro_themelastlogin]      // Backward compatibility





/************************************
	Naviagtion Menu CSS Clearing
 ************************************/

// Remove the <div> surrounding the dynamic navigation to cleanup markup
function zifa_website_wp_nav_menu_args($args = '')
{
    $args['container'] = false;
    return $args;
}
// Remove Injected classes, ID's and Page ID's from Navigation <li> items
function zifa_website_css_attributes_filter($var)
{
    return is_array($var) ? array() : '';
}

add_filter('wp_nav_menu_args', 'zifa_website_wp_nav_menu_args'); // Remove surrounding <div> from WP Navigation
// add_filter('nav_menu_css_class', 'zifa_website_css_attributes_filter', 100, 1); // Remove Navigation <li> injected classes
add_filter('nav_menu_item_id', 'zifa_website_css_attributes_filter', 100, 1); // Remove Navigation <li> injected ID
add_filter('page_css_class', 'zifa_website_css_attributes_filter', 100, 1); // Remove Navigation <li> Page ID's






/************************************
	Pagination - News and etc... IF added
 ************************************/
function pagination($pages = '', $range = 4)
{

    $showitems = ($range * 2) + 1;

    global $paged;
    if (empty($paged)) $paged = 1;

    if ($pages == '') {
        global $wp_query;
        $pages = $wp_query->max_num_pages;

        if (!$pages) {
            $pages = 1;
        }
    }

    if (1 != $pages) {
        echo "<div class=\"pagination\"><span>Page " . $paged . " of " . $pages . "</span>";
        if ($paged > 2 && $paged > $range + 1 && $showitems < $pages) echo "<a href='" . get_pagenum_link(1) . "'>&laquo; First</a>";
        if ($paged > 1 && $showitems < $pages) echo "<a href='" . get_pagenum_link($paged - 1) . "'>&lsaquo; Previous</a>";

        for ($i = 1; $i <= $pages; $i++) {
            if (1 != $pages && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems)) {
                echo ($paged == $i) ? "<span class=\"current\">" . $i . "</span>" : "<a href='" . get_pagenum_link($i) . "' class=\"inactive\">" . $i . "</a>";
            }
        }

        if ($paged < $pages && $showitems < $pages) echo "<a href=\"" . get_pagenum_link($paged + 1) . "\">Next &rsaquo;</a>";
        if ($paged < $pages - 1 &&  $paged + $range - 1 < $pages && $showitems < $pages) echo "<a href='" . get_pagenum_link($pages) . "'>Last &raquo;</a>";
        echo "</div>\n";
    }
}






/************************************
	Disable Gutenberg Blocks
    https://www.wppagebuilders.com/disable-gutenberg-blocks/
    https://developer.wordpress.org/block-editor/reference-guides/core-blocks/
 ************************************/

// function zifa_website_allowed_block_types ( $block_editor_context, $editor_context ) {
// 	if ( ! empty( $editor_context->post ) ) {
// 		return array(
//             'core/buttons',
//             'core/columns',
//             // 'core/embed',
//             'core/heading',
// 			'core/image',
//             'core/paragraph',
//             'core/list',
//             'core/html',
//             'core/text-columns',
//             'core/separator',
//             'core/spacer',
//             'core/shortcode',
//             'core/table',

//             // Custom Additions
//             'contact-form-7/contact-form-selector',
//             'carbon-fields/national-team-card',
// 		);
// 	}
// 	return $block_editor_context;
// }
// add_filter( 'allowed_block_types_all', 'zifa_website_allowed_block_types', 10, 2 );


// 
// Helper Function to Load Countries from JSON File, Used in Carbon Fields Select Field
//

function get_countries_from_json()
{
    static $options = null;
    if ($options !== null) return $options;

    $transient_name = 'countries_json_cache';
    $options = get_transient($transient_name);

    if (false === $options) {
        $json_file = get_template_directory() . '/api/countries.json'; // Adjust path if needed

        if (! file_exists($json_file)) {
            return array();
        }

        $json_data = file_get_contents($json_file);
        $countries = json_decode($json_data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return array();
        }

        $options = array();
        foreach ($countries as $country) {
            $code = $country['code'] ?? '';
            $name = $country['name'] ?? '';
            if ($code && $name) {
                $options[$code] = $name;
            }
        }
        asort($options);
        set_transient($transient_name, $options, DAY_IN_SECONDS);
    }

    return $options;
}

// Helper: get match type options from fixtures/results CPT
function zifa_get_match_type_options()
{
    $ids = get_posts([
        'post_type'      => 'fixtures-results',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    ]);

    $options = [];
    foreach ($ids as $pid) {
        $type = function_exists('carbon_get_post_meta') ? (string) carbon_get_post_meta($pid, 'fixture_match_type') : '';
        if ($type === '') {
            $type = (string) get_post_meta($pid, '_fixture_match_type', true);
        }
        $type = trim($type);
        if ($type === '') continue;
        $options[$type] = $type;
    }

    ksort($options);
    return $options;
}

// Helper: get league standings posts for select options
function zifa_get_league_standings_options()
{
    $posts = get_posts([
        'post_type'      => 'league-standings',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    $options = [];
    foreach ($posts as $p) {
        $title = get_post_meta($p->ID, 'zifa_league_title', true);
        if ($title === '') $title = get_post_meta($p->ID, '_zifa_league_title', true);
        $group = get_post_meta($p->ID, 'zifa_league_group', true);
        if ($group === '') $group = get_post_meta($p->ID, '_zifa_league_group', true);

        $label = $title ? $title : $p->post_title;
        if ($group !== '') $label .= ' - Group ' . $group;

        $options[$p->ID] = $label;
    }

    return $options;
}




/************************************

	Carbon Fields
    
 ************************************/

use Carbon_Fields\Container;
use Carbon_Fields\Block;
use Carbon_Fields\Field;


// Register custom block category
add_filter('block_categories_all', function ($categories, $post) {
    return array_merge($categories, array(
        array(
            'slug'  => 'zifa-blocks',
            'title' => __('ZIFA Blocks'),
            'icon'  => 'groups',
        )
    ));
}, 10, 2);


add_action('carbon_fields_register_fields', 'zifa_website_custom_theme_options');
function zifa_website_custom_theme_options()
{
    // 
    // Theme Options Page
    // 
    Container::make('theme_options', __('Theme Settings'))
        // Default is gear icon
        ->set_icon('dashicons-buddicons-topics')

        ->set_page_menu_title('Theme Settings')

        // Position guide - https://developer.wordpress.org/reference/functions/add_menu_page/
        ->set_page_menu_position(75)

        ->add_tab(__('Contact Details'), array(
            Field::make('text', 'zifa_website_address', __('Address'))
                ->set_width(100)
                ->set_attribute('placeholder', '57 Livingstone Ave, Harare, Zimbabwe'),

            Field::make('text', 'zifa_website_email', __('Email'))
                ->set_width(50)
                ->set_attribute('placeholder', 'info@zifa.co.zw'),

            Field::make('text', 'zifa_website_number', __('Mobile/Phone Number'))
                ->set_width(50)
                ->set_attribute('placeholder', '+263 242 798 627'),

            Field::make('text', 'zifa_website_contact-form', __('Contact Form'))
                ->set_width(50)
                ->set_attribute('placeholder', 'paste short code...'),

            Field::make('textarea', 'zifa_website_google_map', __('Google Map'))
                ->set_width(50)
                ->set_rows(7)
                ->set_attribute('placeholder', 'Paste Google map <iframe> here...')
        ))

        ->add_tab(__('Footer Text & Social Media'), array(
            // x f I Y
            Field::make('text', 'zifa_website_custom_tw', __('Twitter/X Link'))
                ->set_width(50),
            Field::make('text', 'zifa_website_custom_fb', __('Facebook Link'))
                ->set_width(50),
            Field::make('text', 'zifa_website_custom_in', __('Instagram Link'))
                ->set_width(50),
            Field::make('text', 'zifa_website_custom_yt', __('Youtube Link'))
                ->set_width(50),
            Field::make('text', 'zifa_website_custom_tt', __('TikTok Link'))
                ->set_width(50),
            Field::make('text', 'zifa_website_custom_wa', __('WhatsApp Channel Link'))
                ->set_width(50),
        ))

        ->add_tab(__('Header & Footer Scripts'), array(
            Field::make('header_scripts', 'zifa_website_header_script', __('Header Scripts'))
                ->set_attribute('placeholder', 'Insert scripts here e.g Google Analytics Code...'),
            // Field::make( 'footer_scripts', 'zifa_website_footer_script', __( 'Footer Scripts' ) )
            //     ->set_attribute( 'placeholder', 'Insert scripts here e.g Google Analytics code... ' ),
        ))

        ->add_tab(__('Home Page'), array(
            Field::make('select', 'zifa_home_league_table', __('Homepage League Table'))
                ->set_options(function () {
                    $opts = zifa_get_league_standings_options();
                    return ['' => 'Latest (Default)'] + $opts;
                })
                ->set_width(100)
        ));


    // 
    // Teams Block Card
    // 
    Block::make(__('Teams Block Card'))
        ->add_fields(array(
            Field::make('text', 'card_heading', __('Team Name')),
            Field::make('image', 'card_image', __('Captain Profile Pic'))
                ->set_width(100),
            Field::make('text', 'btn_text', 'Button Text')
                ->set_width(50),
            Field::make('text', 'btn_link', 'Button URL/Link')
                ->set_width(50),
        ))

        ->set_description(__('Display teams, landing page card with image and link'))

        ->set_icon('groups')

        ->set_category('zifa-blocks')

        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
            // Handle link
            $btn_link = $fields['btn_link'] ?? '';
            $href = $btn_link;
            if ($btn_link && ! preg_match('/^(https?:\/\/|\/\/|#)/', $btn_link)) {
                $href = home_url('/') . ltrim($btn_link, '/');
            }

            // Handle image
            $image_url = !empty($fields['card_image']) ? wp_get_attachment_image_url($fields['card_image'], 'full') : '';
            $alt = !empty($fields['card_heading']) ? $fields['card_heading'] : 'Team Member';

            // Background image fallback
            $bg_url = get_theme_file_uri('/img/team/team-bg.webp');

?>

        <div class="card teams">
            <h3 class="card-title"><?php echo esc_html($fields['card_heading']); ?></h3>
            <?php if ($image_url): ?>
                <a href="<?= esc_url($href) ?>">
                    <img src="<?= esc_url($image_url) ?>" alt="<?= esc_attr($alt) ?>" class="img-fluid">
                </a>
            <?php endif; ?>
            <?php if ($btn_link): ?>
                <a href="<?= esc_url($href) ?>" class="card-link">
                    <?= esc_html($fields['btn_text'] ?? 'Learn More') ?>
                </a>
            <?php endif; ?>
        </div>
    <?php
        });


    // 
    // Members & Players Block Card
    // 
    Block::make(__('Members & Players Block Card'))
        ->add_fields(array(
            Field::make('image', 'card_image', __('Profile Picture')),
            Field::make('text', 'member_name', __('Full Name')),
            Field::make('date', 'member_dob', __('DOB'))
                ->set_storage_format('Y-m-d'),
            Field::make('text', 'member_position', __('Position')),
            Field::make('text', 'member_caps', __('Caps')),
            Field::make('text', 'member_club', __('Club')),

        ))

        ->set_description(__('Display teams, staff members, etc as cards with image and link'))

        ->set_icon('admin-users')

        ->set_category('zifa-blocks')

        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {

            // Handle image
            $image_url = !empty($fields['card_image']) ? wp_get_attachment_image_url($fields['card_image'], 'full') : '';
            $alt = !empty($fields['member_name']) ? $fields['member_name'] : 'Team Member';

            // Format date.
            $dob_formatted = '';
            if (! empty($fields['member_dob'])) {
                $dob_date = date_create($fields['member_dob']);
                if ($dob_date) {
                    $dob_formatted = date_format($dob_date, 'd M Y');
                } else {
                    $dob_formatted = $fields['member_dob'];
                }
            }

    ?>

        <div class="card players">
            <?php if ($image_url): ?>
                <img src="<?= esc_url($image_url) ?>" alt="<?= esc_attr($alt) ?>" class="card-img-top">
            <?php else: ?>
                <img class="card-img-top" src="<?php echo get_stylesheet_directory_uri(); ?>/img/team/team-placeholder.png" />
            <?php endif; ?>

            <div class="card-body bg-primary text-white">
                <h5 class="card-title"><?php echo esc_html($fields['member_name']); ?></h5>
                <p class="card-text">DOB: <?php echo esc_html($dob_formatted); ?></p>
                <p class="card-text">Position: <?php echo esc_html($fields['member_position']); ?></p>
                <p class="card-text">Caps: <?php echo esc_html($fields['member_caps']); ?></p>
                <p class="card-text">Club: <?php echo esc_html($fields['member_club']); ?></p>
            </div>
        </div>

    <?php
        });

    // 
    // Staff & Committes Block Card
    // 
    Block::make(__('Staff & Committes Block Card'))
        ->add_fields(array(
            Field::make('image', 'card_image', __('Profile Picture')),
            Field::make('text', 'member_name', __('Full Name')),
            Field::make('text', 'member_position', __('Position')),
        ))

        ->set_description(__('Display Staff & Committes as cards with image and position'))

        ->set_icon('admin-users')

        ->set_category('zifa-blocks')

        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {

            // Handle image
            $image_url = !empty($fields['card_image']) ? wp_get_attachment_image_url($fields['card_image'], 'full') : '';
            $alt = !empty($fields['member_name']) ? $fields['member_name'] : 'Team Member';

    ?>

        <div class="card players">
            <?php if ($image_url): ?>
                <img src="<?= esc_url($image_url) ?>" alt="<?= esc_attr($alt) ?>" class="card-img-top">
            <?php else: ?>
                <img class="card-img-top" src="<?php echo get_stylesheet_directory_uri(); ?>/img/team/team-placeholder.png" />
            <?php endif; ?>

            <div class="card-body bg-primary text-white">
                <h5 class="card-title"><?php echo esc_html($fields['member_name']); ?></h5>
                <p class="card-text"><?php echo esc_html($fields['member_position']); ?></p>
            </div>
        </div>

    <?php
        });

    // 
    // Landing Page Block Card
    // 
    Block::make(__('Landing Page Block Card'))
        ->add_fields(array(
            Field::make('text', 'title_name', __('Page Title')),
            Field::make('image', 'card_image', __('Page Image')),
            Field::make('text', 'page_link', __('Page Link')),
        ))

        ->set_description(__('Display teams, staff members, etc as cards with image and link'))

        ->set_icon('list-view') // layout

        ->set_category('zifa-blocks')

        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {

            // Handle image
            $image_url = !empty($fields['card_image']) ? wp_get_attachment_image_url($fields['card_image'], 'full') : '';
            $alt = !empty($fields['member_name']) ? $fields['member_name'] : 'Team Member';

            $btn_link = $fields['page_link'] ?? '';
            $href = $btn_link;
            if ($btn_link && ! preg_match('/^(https?:\/\/|\/\/|#)/', $btn_link)) {
                $href = home_url('/') . ltrim($btn_link, '/');
            }
    ?>

        <div class="card content">
            <?php if ($image_url): ?>
                <img class="card-img" alt="<?= esc_attr($alt) ?>" src="<?= esc_url($image_url) ?>">
            <?php else: ?>
                <img class="card-img" alt="<?= esc_attr($alt) ?>" src="<?php echo get_stylesheet_directory_uri(); ?>/img/default/default-image.jpg" />
            <?php endif; ?>

            <div class="card-img-overlay d-flex flex-column justify-content-center align-items-center text-center">
                <h5 class="card-title text-white"><?php echo esc_html($fields['title_name']); ?></h5>
                <a class="btn btn-primary mt-2" href="<?= esc_url($href) ?>">View</a>
            </div>
        </div>

    <?php
        });


    // 
    // Main Slider - Fields
    //
    Container::make('post_meta', 'ZIFA Slider')
        ->where('post_type', '=', 'zifa-slider')
        ->add_fields(array(
            Field::make('text', 'slider_home_text', 'Description')
                ->set_width(100),
            Field::make('text', 'slider_btn_text', __('Button Text'))
                ->set_width(50),
            Field::make('text', 'slider_btn_url', __('Button URL'))
                ->set_width(50),
            Field::make('checkbox', 'slider_option', 'create external link')
                ->set_width(50)
                ->set_option_value('yes')
                ->set_default_value(false)
                ->set_help_text('Check this box to use external link.'),
            Field::make('checkbox', 'slider_background', 'Remove options background')
                ->set_width(50)
                ->set_option_value('yes')
                ->set_default_value(false)
                ->set_help_text('Check this box to remove banner options background color.'),
        ));


    // Add checkbox to 'posts' post type
    Container::make('post_meta', 'Featured Image Settings')
        ->where('post_type', '=', 'post')
        ->add_fields(array(
            Field::make('checkbox', 'featured_banner', 'Use Featured Image in Banner')
                ->set_option_value('yes')
                ->set_default_value(true) //Set default to checked
        ));



    // Rules & Regs Block
    Block::make(__('Rules And Regs'))
        ->add_fields(array(
            Field::make('text', 'heading', __('Block Heading'))
                ->set_width(50),
            Field::make('image', 'image_rule', __('Block Image'))
                ->set_value_type('url'),
            Field::make('file', 'crb_file', __('File'))
                ->set_type(array('application/pdf'))
                ->set_value_type('url')
                ->set_width(50),
        ))
        ->set_render_callback(function ($fields, $attributes, $inner_blocks) {
    ?>


        <div class="card h-100 d-flex flex-column">
            <div class="card-body d-flex flex-column">

                <img src="<?php echo esc_html($fields['image_rule']); ?>" class="img-fluid mb-3">

                <h4 class="card-title"><?php echo esc_html($fields['heading']); ?></h4>

                <div class="badge text-bg-primary mb-3 align-self-start">
                    <?php echo get_the_date('D j M Y'); ?>
                </div>


                <div class="mt-auto">
                    <a href="<?php echo esc_url($fields['crb_file']); ?>" target="_blank" class="btn btn-primary">Read More</a>
                </div>
            </div>

        </div>

        <?php
        });





    // 
    // Fixtures & Results- Fields
    //
    Container::make('post_meta', 'Fixtures & Results')
        ->where('post_type', '=', 'fixtures-results')
        ->add_fields(array(


            // ==============================
            // SECTION: Match Details
            // ==============================

            Field::make('date', 'fixture_date', 'Match Date')
                ->set_storage_format('Y-m-d') // This ensures proper sorting
                ->set_width(30),

            Field::make('text', 'fixture_time', 'Kick-off Time')
                ->set_width(30)
                ->set_attribute('placeholder', 'e.g., 15:00'),

            Field::make('text', 'fixture_match_number', 'Match Number')
                ->set_width(20)
                ->set_attribute('placeholder', 'e.g., M1'),

            Field::make('select', 'fixture_gender', 'Gender')
                ->set_options(array(
                    'men'   => 'Men',
                    'women' => 'Women'
                ))
                ->set_width(20),

            Field::make('select', 'fixture_age_group', 'Age Group')
                ->set_options(array(
                    'senior' => 'Senior',
                    'u13'    => 'U13',
                    'u15'    => 'U15',
                    'u17'    => 'U17',
                    'u20'    => 'U20',
                    'u23'    => 'U23',
                ))
                ->set_width(20),

            Field::make('select', 'fixture_match_type', 'Match Type')
                ->set_options(array(
                    'Africa Cup of Nations (AFCON)' => 'Africa Cup of Nations (AFCON)',
                    'CAF World Cup Qualifiers' => 'CAF World Cup Qualifiers',
                    'COSAFA Cup' => 'COSAFA Cup',
                    'COSAFA Women\'s Championship' => 'COSAFA Women\'s Championship',
                    'FIFA World Cup' => 'FIFA World Cup',
                    'Friendly' => 'Friendly',
                    'U-17 Women\'s World Cup Qualifiers' => 'U-17 Women\'s World Cup Qualifiers',
                ))
                ->set_width(30),

            Field::make('text', 'fixture_stadium', 'Stadium')
                ->set_width(30),

            Field::make('select', 'fixture_group_number', 'Group Number')
                ->set_options(array(
                    'A' => 'Group A',
                    'B' => 'Group B',
                    'C' => 'Group C',
                    'D' => 'Group D',
                ))
                ->set_width(20),

            Field::make('select', 'fixture_country_home', 'Home Team')
                ->set_options(get_countries_from_json())
                ->set_width(30),

            Field::make('select', 'fixture_country_away', 'Away Team')
                ->set_options(get_countries_from_json())
                ->set_width(30),

            Field::make('select', 'fixture_match_status', 'Match Status')
                // ->set_type( 'radio' )
                ->set_options(array(
                    'fixture' => 'Fixture (Upcoming)',
                    'result'  => 'Result (Played)',
                ))
                ->set_width(100)
                ->set_help_text('Choose "Result" to unlock score and statistics fields.'),

            // ==============================
            // SECTION: Results & Statistics (Conditional)
            // Only shown when Match Status = "Result"
            // ==============================

            Field::make('separator', 'crb_separator_results', 'Match Results & Statistics')
                ->set_conditional_logic(array(
                    array(
                        'field' => 'fixture_match_status',
                        'value' => 'result',
                    ),
                )),

            Field::make('text', 'fixture_country_home_score', 'Home Team Score')
                ->set_attribute('type', 'number')
                ->set_attribute('min', 0)
                ->set_width(25)
                ->set_conditional_logic(array(
                    array(
                        'field' => 'fixture_match_status',
                        'value' => 'result',
                    ),
                )),

            Field::make('text', 'fixture_country_away_score', 'Away Team Score')
                ->set_attribute('type', 'number')
                ->set_attribute('min', 0)
                ->set_width(25)
                ->set_conditional_logic(array(
                    array(
                        'field' => 'fixture_match_status',
                        'value' => 'result',
                    ),
                )),

            // Match Statistics (Possession, Shots, Cards)
            Field::make('complex', 'match_stats', 'Match Statistics')
                ->set_layout('tabbed-horizontal')
                ->set_max(1)
                ->set_conditional_logic(array(
                    array(
                        'field' => 'fixture_match_status',
                        'value' => 'result',
                    ),
                ))
                ->add_fields(array(
                    Field::make('text', 'possession_home', 'Home Possession (%)')
                        ->set_attribute('type', 'number')
                        ->set_attribute('min', 0)
                        ->set_attribute('max', 100)
                        ->set_width(50),
                    Field::make('text', 'possession_away', 'Away Possession (%)')
                        ->set_attribute('type', 'number')
                        ->set_attribute('min', 0)
                        ->set_attribute('max', 100)
                        ->set_width(50),

                    Field::make('text', 'shots_on_target_home', 'Home Shots on Target')
                        ->set_attribute('type', 'number')
                        ->set_width(50),
                    Field::make('text', 'shots_on_target_away', 'Away Shots on Target')
                        ->set_attribute('type', 'number')
                        ->set_width(50),

                    Field::make('text', 'yellow_cards_home', 'Home Yellow Cards')
                        ->set_attribute('type', 'number')
                        ->set_width(50),
                    Field::make('text', 'yellow_cards_away', 'Away Yellow Cards')
                        ->set_attribute('type', 'number')
                        ->set_width(50),

                    Field::make('text', 'red_cards_home', 'Home Red Cards')
                        ->set_attribute('type', 'number')
                        ->set_width(50),
                    Field::make('text', 'red_cards_away', 'Away Red Cards')
                        ->set_attribute('type', 'number')
                        ->set_width(50),
                ))
                ->set_help_text('Enter match statistics (only visible for completed matches).'),

            // Goals Scored
            Field::make('complex', 'match_goals', 'Goals Scored')
                ->setup_labels(array(
                    'plural_name'   => 'Goals',
                    'singular_name' => 'Goal'
                ))
                ->set_conditional_logic(array(
                    array(
                        'field' => 'fixture_match_status',
                        'value' => 'result',
                    ),
                ))
                ->add_fields(array(
                    Field::make('select', 'team', 'Team')
                        ->set_options(array(
                            'home' => 'Home',
                            'away' => 'Away'
                        ))
                        ->set_width(30),
                    Field::make('text', 'player', 'Player Name')
                        ->set_width(50),
                    Field::make('text', 'minute', 'Minute')
                        ->set_width(20)
                        ->set_attribute('placeholder', '89'),
                ))
                ->set_collapsed(true)
                ->set_max(20)
                ->set_help_text('List all goal scorers and minutes.'),

            // Additional Match Info
            Field::make('text', 'match_duration', 'Match Duration')
                ->set_conditional_logic(array(
                    array(
                        'field' => 'fixture_match_status',
                        'value' => 'result',
                    ),
                ))
                ->set_width(30)
                ->set_help_text('e.g., 90 min, 5 min (extra time)')
                ->set_attribute('placeholder', '90 min'),

            Field::make('text', 'match_referee', 'Referee Name')
                ->set_conditional_logic(array(
                    array(
                        'field' => 'fixture_match_status',
                        'value' => 'result',
                    ),
                ))
                ->set_width(30)
                ->set_attribute('placeholder', 'Referee Name'),

            Field::make('text', 'match_attendance', 'Attendance')
                ->set_conditional_logic(array(
                    array(
                        'field' => 'fixture_match_status',
                        'value' => 'result',
                    ),
                ))
                ->set_attribute('type', 'number')
                ->set_width(30)
                ->set_attribute('min', 0)
                ->set_help_text('Total number of spectators')
                ->set_attribute('placeholder', 'e.g., 45000'),

        ));

    // ==============================
    // League Standings - Fields (POST META)
    // ==============================

    Container::make('post_meta', 'League Standings')
        ->where('post_type', '=', 'league-standings')
        ->add_fields(array(

            Field::make('select', 'zifa_league_title', 'Match Type')
                ->set_options(function () {
                    $opts = zifa_get_match_type_options();
                    return ['' => 'Select match type'] + $opts;
                })
                ->set_width(34),

            Field::make('select', 'zifa_league_group', 'Group')
                ->set_options(array(
                    'A' => 'Group A',
                    'B' => 'Group B',
                    'C' => 'Group C',
                    'D' => 'Group D',
                ))
                ->set_width(16),

            Field::make('text', 'zifa_league_season', 'Season / Round Label')
                ->set_default_value('2025/26')
                ->set_width(50),

            Field::make('complex', 'zifa_league_table', 'Teams Table')
                ->set_layout('tabbed-horizontal')
                ->set_collapsed(true)
                ->add_fields(array(

                    Field::make('text', 'club', 'Club Name/Team Name')
                        ->set_width(30)
                        ->set_attribute('placeholder', 'e.g., Dynamos'),

                    Field::make('text', 'played', 'P')
                        ->set_attribute('type', 'number')
                        ->set_attribute('min', 0)
                        ->set_default_value(0)
                        ->set_width(10),

                    Field::make('text', 'wins', 'W')
                        ->set_attribute('type', 'number')
                        ->set_attribute('min', 0)
                        ->set_default_value(0)
                        ->set_width(10),

                    Field::make('text', 'draws', 'D')
                        ->set_attribute('type', 'number')
                        ->set_attribute('min', 0)
                        ->set_default_value(0)
                        ->set_width(10),

                    Field::make('text', 'losses', 'L')
                        ->set_attribute('type', 'number')
                        ->set_attribute('min', 0)
                        ->set_default_value(0)
                        ->set_width(10),

                    Field::make('text', 'goals_for', 'GF')
                        ->set_attribute('type', 'number')
                        ->set_attribute('min', 0)
                        ->set_default_value(0)
                        ->set_width(10),

                    Field::make('text', 'goals_against', 'GA')
                        ->set_attribute('type', 'number')
                        ->set_attribute('min', 0)
                        ->set_default_value(0)
                        ->set_width(10),

                    Field::make('text', 'points', 'PTS')
                        ->set_attribute('type', 'number')
                        ->set_attribute('min', 0)
                        ->set_default_value(0)
                        ->set_width(10)
                )),
        ));



    // 
    // ZIFA Videos - Fields
    //
    Container::make('post_meta', 'ZIFA Videos')
        ->where('post_type', '=', 'zifa-videos')
        ->add_fields(array(
            Field::make('textarea', 'zifa_video', 'Youtube iFrame')
                ->set_attribute('Paste video iframe here...')
                ->set_width(100),
        ));
}


function zifa_website_league_standings_post_type()
{
    $labels = array(
        'name'               => _x('League Standings', 'Post type general name', 'text_domain'),
        'singular_name'      => _x('League Table', 'Post type singular name', 'text_domain'),
        'menu_name'          => __('League Standings', 'text_domain'),
        'add_new'            => __('Add New Table', 'text_domain'),
        'add_new_item'       => __('Add New League Table', 'text_domain'),
        'new_item'           => __('New League Table', 'text_domain'),
        'edit_item'          => __('Edit League Table', 'text_domain'),
        'view_item'          => __('View League Table', 'text_domain'),
        'all_items'          => __('All League Tables', 'text_domain'),
        'search_items'       => __('Search League Tables', 'text_domain'),
        'not_found'          => __('No league tables found.', 'text_domain'),
        'not_found_in_trash' => __('No league tables found in Trash.', 'text_domain'),
    );

    $args = array(
        'labels'            => $labels,
        'public'            => false, // admin only
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_admin_bar' => true,
        'show_in_rest'      => true,
        'menu_position'     => 6,
        'menu_icon'         => 'dashicons-awards',
        'supports'          => array('title'),
        'has_archive'       => false,
        'rewrite'           => false,
        'capability_type'   => 'post',
    );

    register_post_type('league-standings', $args);
}
add_action('init', 'zifa_website_league_standings_post_type');


add_action('wp_ajax_hc_filter_by_date', 'hc_filter_by_date');
add_action('wp_ajax_nopriv_hc_filter_by_date', 'hc_filter_by_date');

function hc_filter_by_date()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hc_calendar_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce'], 403);
    }

    $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
    if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        wp_send_json_error(['message' => 'Invalid date'], 400);
    }

    $date_start = $date . ' 00:00:00';
    $date_end   = $date . ' 23:59:59';

    $match_type = isset($_POST['match_type']) ? sanitize_text_field($_POST['match_type']) : '';
    $layout = isset($_POST['layout']) ? sanitize_text_field($_POST['layout']) : 'list';
    $tile_cols = isset($_POST['tile_cols']) ? (int) $_POST['tile_cols'] : 2;
    $is_tile = ($layout === 'tile');
    $col_class = ($tile_cols === 1) ? 'col-12' : 'col-12 col-md-6';

    $countries = function_exists('get_countries_from_json') ? get_countries_from_json() : [];

    // FIXTURES
    $fixture_meta_query = [
        'relation' => 'AND',
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
        [
            'relation' => 'OR',
            [
                'key'     => 'fixture_date',
                'value'   => [$date_start, $date_end],
                'compare' => 'BETWEEN',
                'type'    => 'DATETIME',
            ],
            [
                'key'     => '_fixture_date',
                'value'   => [$date_start, $date_end],
                'compare' => 'BETWEEN',
                'type'    => 'DATETIME',
            ],
        ],
    ];

    if ($match_type !== '') {
        $fixture_meta_query[] = [
            'relation' => 'OR',
            [
                'key'     => 'fixture_match_type',
                'value'   => $match_type,
                'compare' => '=',
            ],
            [
                'key'     => '_fixture_match_type',
                'value'   => $match_type,
                'compare' => '=',
            ],
        ];
    }

    $fixture_ids = get_posts([
        'post_type'      => 'fixtures-results',
        'posts_per_page' => 50,
        'post_status'    => 'publish',
        'meta_query'     => $fixture_meta_query,
        'fields' => 'ids',
    ]);

    ob_start();
    if ($is_tile) {
        echo '<div class="row g-3">';
    }
    if (!empty($fixture_ids)) {
        foreach ($fixture_ids as $post_id) {
            $home_code = carbon_get_post_meta($post_id, 'fixture_country_home') ?: '';
            $away_code = carbon_get_post_meta($post_id, 'fixture_country_away') ?: '';
            $kickoff   = carbon_get_post_meta($post_id, 'fixture_time') ?: 'TBD';
            $match_type = trim((string) carbon_get_post_meta($post_id, 'fixture_match_type'));
            $stadium = trim((string) carbon_get_post_meta($post_id, 'fixture_stadium'));
            $group_number = trim((string) carbon_get_post_meta($post_id, 'fixture_group_number'));

            $home_name = $home_code ? ($countries[$home_code] ?? 'Home Team') : 'Home';
            $away_name = $away_code ? ($countries[$away_code] ?? 'Away Team') : 'Away';

            $ts = strtotime($date);
            $date_human = $ts ? date('F j, Y', $ts) : 'Date TBA';

            $permalink = get_permalink($post_id);
        ?>
            <?php if ($is_tile) : ?>
                <div class="<?php echo esc_attr($col_class); ?>">
                <?php endif; ?>
                <a class="hc-mini-row<?php echo $is_tile ? ' hc-mini-row--tile' : ''; ?>" href="<?php echo esc_url($permalink); ?>">
                    <div class="hc-mini-row__side">
                        <?php if ($home_code) : ?>
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
                        <div class="hc-mini-row__meta"><?php echo esc_html($kickoff); ?></div>
                        <div class="hc-mini-row__match"><?php echo esc_html(strtoupper($home_name . ' v ' . $away_name)); ?></div>
                        <?php if ($stadium || $group_number) : ?>
                            <div class="hc-mini-row__extras">
                                <?php if ($stadium) : ?>
                                    <span class="hc-mini-row__chip"><?php echo esc_html($stadium); ?></span>
                                <?php endif; ?>
                                <?php if ($group_number) : ?>
                                    <?php $group_label = preg_match('/^group\s+/i', $group_number) ? $group_number : ('Group ' . $group_number); ?>
                                    <span class="hc-mini-row__chip"><?php echo esc_html($group_label); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="hc-mini-row__side">
                        <?php if ($away_code) : ?>
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
    } else {
        echo '<div class="hc-mini-empty">No fixtures on this date.</div>';
    }
    if ($is_tile) {
        echo '</div>';
    }
    $fixtures_html = ob_get_clean();

    // RESULTS
    $result_meta_query = [
        'relation' => 'AND',
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
        [
            'relation' => 'OR',
            [
                'key'     => 'fixture_date',
                'value'   => [$date_start, $date_end],
                'compare' => 'BETWEEN',
                'type'    => 'DATETIME',
            ],
            [
                'key'     => '_fixture_date',
                'value'   => [$date_start, $date_end],
                'compare' => 'BETWEEN',
                'type'    => 'DATETIME',
            ],
        ],
    ];

    if ($match_type !== '') {
        $result_meta_query[] = [
            'relation' => 'OR',
            [
                'key'     => 'fixture_match_type',
                'value'   => $match_type,
                'compare' => '=',
            ],
            [
                'key'     => '_fixture_match_type',
                'value'   => $match_type,
                'compare' => '=',
            ],
        ];
    }

    $result_ids = get_posts([
        'post_type'      => 'fixtures-results',
        'posts_per_page' => 50,
        'post_status'    => 'publish',
        'meta_query'     => $result_meta_query,
        'fields' => 'ids',
    ]);

    ob_start();
    if ($is_tile) {
        echo '<div class="row g-3">';
    }
    if (!empty($result_ids)) {
        foreach ($result_ids as $post_id) {
            $home = carbon_get_post_meta($post_id, 'fixture_country_home') ?: '';
            $away = carbon_get_post_meta($post_id, 'fixture_country_away') ?: '';
            $match_type = trim((string) carbon_get_post_meta($post_id, 'fixture_match_type'));
            $stadium = trim((string) carbon_get_post_meta($post_id, 'fixture_stadium'));
            $group_number = trim((string) carbon_get_post_meta($post_id, 'fixture_group_number'));

            $home_name = $home ? ($countries[$home] ?? 'Home Team') : 'Home';
            $away_name = $away ? ($countries[$away] ?? 'Away Team') : 'Away';

            $home_score = carbon_get_post_meta($post_id, 'fixture_country_home_score');
            $away_score = carbon_get_post_meta($post_id, 'fixture_country_away_score');

            $show_score = is_numeric($home_score) && is_numeric($away_score);
            $score_text = $show_score ? ($home_score . ' - ' . $away_score) : '–';

            $ts = strtotime($date);
            $date_human = $ts ? date('F j, Y', $ts) : 'Date TBA';

            $permalink = get_permalink($post_id);
        ?>
            <?php if ($is_tile) : ?>
                <div class="<?php echo esc_attr($col_class); ?>">
                <?php endif; ?>
                <a class="hc-mini-row<?php echo $is_tile ? ' hc-mini-row--tile' : ''; ?>" href="<?php echo esc_url($permalink); ?>">
                    <div class="hc-mini-row__mid" style="grid-column:1 / -1;">
                        <?php if ($match_type) : ?>
                            <div class="hc-mini-row__type"><?php echo esc_html($match_type); ?></div>
                        <?php endif; ?>
                        <div class="hc-mini-row__date"><?php echo esc_html($date_human); ?></div>
                        <div class="hc-mini-row__meta"><?php echo esc_html($score_text); ?></div>
                        <div class="hc-mini-row__match"><?php echo esc_html(strtoupper($home_name . ' v ' . $away_name)); ?></div>
                        <?php if ($stadium || $group_number) : ?>
                            <div class="hc-mini-row__extras">
                                <?php if ($stadium) : ?>
                                    <span class="hc-mini-row__chip"><?php echo esc_html($stadium); ?></span>
                                <?php endif; ?>
                                <?php if ($group_number) : ?>
                                    <?php $group_label = preg_match('/^group\s+/i', $group_number) ? $group_number : ('Group ' . $group_number); ?>
                                    <span class="hc-mini-row__chip"><?php echo esc_html($group_label); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </a>
                <?php if ($is_tile) : ?>
                </div>
            <?php endif; ?>
    <?php
        }
    } else {
        echo '<div class="hc-mini-empty">No results on this date.</div>';
    }
    if ($is_tile) {
        echo '</div>';
    }
    $results_html = ob_get_clean();

    wp_send_json_success([
        'fixtures_html' => $fixtures_html,
        'results_html'  => $results_html,
        'date'          => $date,
    ]);
}








// 
// Main Slider - Post Type
//
function zifa_website_miracle_post_type()
{
    register_post_type(
        'zifa-slider',
        array(
            'labels' => array(
                'name' => __('ZIFA Slider'),
                'singular_name' => __('ZIFA Slider'),
                'add_new_item' => 'Add New ZIFA Slider',
                'add_new' => __('Add New ZIFA Slider'),
                'attributes' => __('ZIFA Slider Attributes', 'text_domain'),
            ),
            'public' => true,
            'hierarchical' => false, // Enables parent-child relationships
            // 'publicly_queryable' => true, // explicitly set if needed
            // 'has_archive'        => true,
            // 'show_in_rest'       => true, // Enables Gutenberg support
            'rewrite' => array(
                'slug' => 'zifa-slider'
            ),
            'supports' => array(
                'title',
                'thumbnail', // Featured image
                // 'editor', // Content editor
                'page-attributes' // Page attributes allow parent assignment
            ),
            'menu_position' => 5,
            'menu_icon' => __('dashicons-images-alt2')
        )
    );
}
add_action('init', 'zifa_website_miracle_post_type');





// 
// Partners Slider
//
function zifa_website_partners_slider()
{
    register_post_type(
        'partners-slider',
        array(
            'labels' => array(
                'name' => __('Partners Slider'),
                'singular_name' => __('Partners Slider'),
                'add_new_item' => 'Add New Partners Slider',
                'add_new' => __('Add New Partners Slider'),
                'attributes' => __('Partners Slider Attributes', 'text_domain'),
            ),
            'public' => true,
            // 'hierarchical' => false, // Enables parent-child relationships
            // 'publicly_queryable' => true, // explicitly set if needed
            // 'has_archive'        => true,
            // 'show_in_rest'       => true, // Enables Gutenberg support
            'rewrite' => array(
                'slug' => 'partners-slider'
            ),
            'supports' => array(
                'title',
                'thumbnail',
                // 'editor', // Content editor
                // 'page-attributes' // Page attributes allow parent assignment
            ),
            'menu_position' => 5,
            'menu_icon' => __('dashicons-images-alt2')
        )
    );
}
add_action('init', 'zifa_website_partners_slider');



// 
// ZIFA Videos - Post Type
//
function zifa_website_videos()
{
    register_post_type(
        'zifa-videos',
        array(
            'labels' => array(
                'name' => __('Videos'),
                'singular_name' => __('Videos'),
                'add_new_item' => 'Add New Video',
                'add_new' => __('Add New Video'),
                'attributes' => __('Videos Attributes', 'text_domain'),
            ),
            'public' => true,
            'rewrite' => array(
                'slug' => 'zifa-videos'
            ),
            'supports' => array(
                'title',
            ),
            'menu_position' => 5,
            'menu_icon' => __('dashicons-format-video')
        )
    );
}
add_action('init', 'zifa_website_videos');



// 
// Action Zone - Post Type
//
function zifa_website_fixtures_and_results()
{
    $labels = array(
        'name'                  => _x('Action Zone', 'Post type general name', 'text_domain'),
        // 'singular_name'         => _x( 'Fixture', 'Post type singular name', 'text_domain' ),
        // 'menu_name'             => _x( 'Action Zone', 'Admin menu name', 'text_domain' ),
        // 'name_admin_bar'        => _x( 'Fixture', 'Add New on Toolbar', 'text_domain' ),
        'add_new'               => __('Add New Action Zone', 'text_domain'),
        'add_new_item'          => __('Add New Action Zone', 'text_domain'),
        'new_item'              => __('New Action Zone', 'text_domain'),
        'edit_item'             => __('Edit Action Zone', 'text_domain'),
        'view_item'             => __('View Action Zone', 'text_domain'),
        'all_items'             => __('All Action Zone', 'text_domain'),
        'search_items'          => __('Search Action Zone', 'text_domain'),
        'parent_item_colon'     => __('Parent Action Zone:', 'text_domain'),
        'not_found'             => __('No Action Zone found.', 'text_domain'),
        'not_found_in_trash'    => __('No Action Zone found in Trash.', 'text_domain'),
        'attributes'            => __('Action Zone Attributes', 'text_domain'),
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __('Post type to manage football fixtures and match results.', 'text_domain'),
        'public'             => true,
        'publicly_queryable' => true,
        'show_in_rest'       => true,  // Enables Gutenberg & REST API support
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_admin_bar'  => true,
        'query_var'          => true,
        'can_export'         => true,
        'has_archive'        => true,  // Enables archive-fixtures-results.php or a page
        'rewrite'            => array(
            'slug'         => 'action-zone', // fixtures-results
            'with_front'   => true,
            'pages'        => true,
            'feeds'        => true,
        ),
        'capability_type'    => 'post',
        'hierarchical'       => false, // No parent/child needed for matches
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-calendar-alt', // More appropriate than images-alt2
        'supports'           => array(
            'title', // Match title (e.g., "ZIM vs ZA")
            // 'editor', // Optional: if you want a content area for previews/reports
            'thumbnail', // Optional: featured image (e.g., match banner)
            // 'excerpt', // Match summary
            // 'page-attributes' // Only if you need menu order or templates
        ),
    );

    register_post_type('fixtures-results', $args);
}
add_action('init', 'zifa_website_fixtures_and_results');


// Add Rules PDF Meta Box
function zifa_add_rules_pdf_metabox()
{
    add_meta_box(
        'zifa_rules_pdf',
        'Rules PDF',
        'zifa_rules_pdf_callback',
        'post',
        'side'
    );
}
add_action('add_meta_boxes', 'zifa_add_rules_pdf_metabox');

function zifa_rules_pdf_callback($post)
{
    $value = get_post_meta($post->ID, 'rules_pdf', true);
    ?>
    <p>
        <label for="rules_pdf">PDF URL:</label><br>
        <input type="text" id="rules_pdf" name="rules_pdf" value="<?php echo esc_attr($value); ?>" style="width:100%;" />
    </p>
    <p>
        <small>Upload your PDF to Media → Copy URL and paste it here.</small>
    </p>
<?php
}

// Save Rules PDF Meta Box
function zifa_save_rules_pdf($post_id)
{
    if (array_key_exists('rules_pdf', $_POST)) {
        update_post_meta(
            $post_id,
            'rules_pdf',
            esc_url_raw($_POST['rules_pdf'])
        );
    }
}
add_action('save_post', 'zifa_save_rules_pdf');





add_action('wp_ajax_hc_calendar', 'hc_calendar_ajax');
add_action('wp_ajax_nopriv_hc_calendar', 'hc_calendar_ajax');

function hc_calendar_ajax()
{
    if (isset($_POST['nonce']) && !wp_verify_nonce($_POST['nonce'], 'hc_calendar_nonce')) {
        wp_send_json_error(['html' => 'Invalid nonce']);
    }

    $month = isset($_POST['month']) ? (int) $_POST['month'] : (int) wp_date('n');
    $year  = isset($_POST['year'])  ? (int) $_POST['year']  : (int) wp_date('Y');
    $match_type = isset($_POST['match_type']) ? sanitize_text_field($_POST['match_type']) : '';

    echo hc_render_mini_calendar($month, $year, $match_type);
    wp_die();
}


function hc_render_mini_calendar($month, $year, $match_type = '')
{
    if ($month < 1) $month = 1;
    if ($month > 12) $month = 12;

    // prev/next
    $prev_m = $month - 1;
    $prev_y = $year;
    if ($prev_m < 1) {
        $prev_m = 12;
        $prev_y--;
    }

    $next_m = $month + 1;
    $next_y = $year;
    if ($next_m > 12) {
        $next_m = 1;
        $next_y++;
    }

    $first_day_ts   = strtotime("$year-$month-01");
    $days_in_month  = (int) date('t', $first_day_ts);
    $start_weekday  = (int) date('w', $first_day_ts);
    $month_label    = date('F Y', $first_day_ts);

    $today_iso = wp_date('Y-m-d');


    $month_start = sprintf('%04d-%02d-01', $year, $month);
    $month_end   = sprintf('%04d-%02d-%02d', $year, $month, $days_in_month);


    $meta_query = [
        'relation' => 'AND',
        [
            'relation' => 'OR',
            [
                'key'     => 'fixture_date',
                'value'   => [$month_start, $month_end],
                'compare' => 'BETWEEN',
                'type'    => 'DATE',
            ],
            [
                'key'     => '_fixture_date',
                'value'   => [$month_start, $month_end],
                'compare' => 'BETWEEN',
                'type'    => 'DATE',
            ],
        ],
        [
            'relation' => 'OR',
            [
                'key'     => 'fixture_match_status',
                'value'   => 'fixture',
                'compare' => '=',
            ],
            [
                'key'     => 'fixture_match_status',
                'value'   => 'result',
                'compare' => '=',
            ],
            [
                'key'     => '_fixture_match_status',
                'value'   => 'fixture',
                'compare' => '=',
            ],
            [
                'key'     => '_fixture_match_status',
                'value'   => 'result',
                'compare' => '=',
            ],
        ],
    ];

    if ($match_type !== '') {
        $meta_query[] = [
            'relation' => 'OR',
            [
                'key'     => 'fixture_match_type',
                'value'   => $match_type,
                'compare' => '=',
            ],
            [
                'key'     => '_fixture_match_type',
                'value'   => $match_type,
                'compare' => '=',
            ],
        ];
    }

    $month_posts = get_posts([
        'post_type'      => 'fixtures-results',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => $meta_query,
        'fields' => 'ids',
    ]);


    $date_flags = [];

    foreach ($month_posts as $pid) {
        $d = function_exists('carbon_get_post_meta') ? (string) carbon_get_post_meta($pid, 'fixture_date') : '';
        if (!$d) continue;


        $d = date('Y-m-d', strtotime($d));


        $status = '';
        if (function_exists('carbon_get_post_meta')) {
            $status = (string) carbon_get_post_meta($pid, 'fixture_match_status');
        }
        if ($status === '') {
            $status = (string) get_post_meta($pid, '_fixture_match_status', true);
        }

        if (!isset($date_flags[$d])) {
            $date_flags[$d] = [
                'has_result' => false,
                'has_future_fixture' => false,
            ];
        }

        if ($status === 'result') {
            $date_flags[$d]['has_result'] = true;
        } elseif ($status === 'fixture') {

            if ($d >= $today_iso) {
                $date_flags[$d]['has_future_fixture'] = true;
            }
        }
    }

    $today_y = (int) wp_date('Y');
    $today_m = (int) wp_date('n');
    $today_d = (int) wp_date('j');

    ob_start(); ?>
    <div class="hc-cal">

        <div class="hc-cal__top">
            <button type="button"
                class="hc-cal__nav hc-cal__nav-btn"
                data-year="<?php echo esc_attr($prev_y); ?>"
                data-month="<?php echo esc_attr($prev_m); ?>">
                &laquo;
            </button>

            <div class="hc-cal__title"><?php echo esc_html($month_label); ?></div>

            <button type="button"
                class="hc-cal__nav hc-cal__nav-btn"
                data-year="<?php echo esc_attr($next_y); ?>"
                data-month="<?php echo esc_attr($next_m); ?>">
                &raquo;
            </button>
        </div>

        <div class="hc-cal__grid">
            <div class="hc-cal__dow">S</div>
            <div class="hc-cal__dow">M</div>
            <div class="hc-cal__dow">T</div>
            <div class="hc-cal__dow">W</div>
            <div class="hc-cal__dow">T</div>
            <div class="hc-cal__dow">F</div>
            <div class="hc-cal__dow">S</div>

            <?php
            for ($i = 0; $i < $start_weekday; $i++) {
                echo '<div class="hc-cal__cell hc-cal__cell--blank"></div>';
            }

            for ($day = 1; $day <= $days_in_month; $day++) {
                $is_today  = ($year === $today_y && $month === $today_m && $day === $today_d);

                $iso = sprintf('%04d-%02d-%02d', $year, $month, $day);

                $has_result = !empty($date_flags[$iso]['has_result']);
                $has_future_fixture = !empty($date_flags[$iso]['has_future_fixture']);

                $has_match = false;
                if ($iso < $today_iso) {
                    $has_match = $has_result;
                } else {
                    $has_match = ($has_result || $has_future_fixture);
                }

                $classes = 'hc-cal__cell';
                if ($is_today)  $classes .= ' is-today';
                if ($has_match) $classes .= ' has-match';


                $attrs = $has_match ? ' data-date="' . esc_attr($iso) . '" role="button" tabindex="0"' : '';

                echo '<div class="' . esc_attr($classes) . '"' . $attrs . '>';
                echo '<span class="hc-cal__num">' . esc_html($day) . '</span>';
                if ($has_match) echo '<span class="hc-cal__dot" aria-hidden="true"></span>';
                echo '</div>';
            }
            ?>
        </div>

        <div class="hc-cal__legend">
            <span class="hc-cal__legend-dot"></span> Match day
        </div>

    </div>
<?php
    return ob_get_clean();
}






/************************************
    Custom Login Form
    Author: thatAfro
    Author URI: https://thatafro.netlify.app/
 ************************************/

// Changes the Logo
function zifa_website_login_form_customization()
{
?>
    <style type="text/css">
        body {
            /* background-color: white !important; */
            background: black url('<?php echo get_theme_file_uri() . '/img/login-bg.jpg' ?>') no-repeat center !important;
            background-size: cover !important;
        }

        #login {
            background: rgba(255, 255, 255, 0.8) !important;
            border-radius: 5px !important;
            width: 320px !important;
            padding: 2% 20px 5px 20px !important;
            margin: auto;
        }

        /* Logo */
        #login h1 a,
        .login h1 a {
            background-image: url('<?php echo get_theme_file_uri() . '/img/zifa-word-mark.svg' ?>') !important;
            width: auto;
            background-size: 250px auto;
            background-repeat: no-repeat;
            margin: 0px !important;
        }

        /* General Form */
        .login form {
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
        }

        .login label {
            color: black !important;
            font-weight: 500;
        }

        .login form .input,
        .login form input[type=checkbox],
        .login input[type=text] {
            background: black;
            border-radius: 0 !important;
        }

        /* Login Error */
        .login #login_error {
            border-left-color: #d63638 !important;
            background-color: #d63638 !important;
            color: white !important;
        }

        .login #login_error a {
            color: white !important;
        }

        /* Other Messages Being Displayed */
        .login .message,
        .login .success {
            border-left-color: #036F3C !important;
            background-color: #036F3C !important;
            color: white !important;
        }

        .login .message a,
        .login .success a {
            color: white !important;
        }

        /* Buttons */
        .wp-core-ui .button-group.button-large .button,
        .wp-core-ui .button.button-large {
            width: 100%;
            min-height: 40px !important;
        }

        .login .button.wp-hide-pw {
            height: 2.35rem !important;
        }

        .wp-core-ui .button {
            background: #036F3C !important;
            border-color: #036F3C !important;
            border-radius: 0 !important;
            color: white !important;
            min-height: 35px !important;
            text-transform: uppercase;
            font-weight: 600;
            margin: 10px 0 0 0 !important;
            transition: all .5s;
        }

        .wp-core-ui .button:hover {
            background: #FFD100 !important;
            border-color: #FFD100 !important;
            color: black !important;
        }

        .login .button.wp-hide-pw {
            top: -9px !important;
        }

        /* Forgot or Back to site links */
        .login #backtoblog a,
        .login #nav a {
            padding-left: 0;
            text-decoration: none;
            color: black !important;
            transition: all .5s;
        }

        .login #backtoblog a:hover,
        .login #nav a:hover {
            padding-left: 5px;
            text-decoration: underline;
        }

        .login #backtoblog,
        .login #nav {
            font-size: 15px !important;
            padding: 0 !important;
        }
    </style>
<?php
}
add_action('login_enqueue_scripts', 'zifa_website_login_form_customization');

// Add link to the logo Image
function zifa_website_login_form_logo_url()
{
    return home_url();
}
add_filter('login_headerurl', 'zifa_website_login_form_logo_url');
