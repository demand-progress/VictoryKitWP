<?

// Exit if accessed directly
if(!defined('ABSPATH')) exit;

$asset_version = '1.0.12';

add_action('wp_enqueue_scripts', 'enqueue_scripts');
function enqueue_scripts() {
    global $asset_version;

    // Google Fonts
    wp_enqueue_style(
        'fonts',
        'https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700'
    );

    // Styles
    wp_enqueue_style(
        'dp-misc',
        // 'https://s3.amazonaws.com/demandprogress/static/css/styles.min.css',
        get_template_directory_uri() . '/legacy/static/css/styles.min.css',
        false,
        $asset_version
    );
    wp_enqueue_style(
        'dp-ak',
        // 'https://s3.amazonaws.com/demandprogress/static/css/styles.ak.css',
        get_template_directory_uri() . '/legacy/static/css/styles.ak.css',
        false,
        $asset_version
    );
    wp_enqueue_style(
        'ak-sample',
        // 'https://act.demandprogress.org/samples/actionkit.css',
        get_template_directory_uri() . '/legacy/ak/actionkit.css',
        false,
        $asset_version
    );
    wp_enqueue_style(
        'style',
        get_stylesheet_uri(),
        false,
        $asset_version
    );

    // Scripts
    wp_enqueue_script(
        'ak',
        // 'https://act.demandprogress.org/resources/actionkit.js',
        get_template_directory_uri() . '/legacy/ak/actionkit.js',
        array('jquery'),
        $asset_version
    );
    wp_enqueue_script(
        'global',
        get_template_directory_uri() . '/js/global.js',
        false,
        $asset_version,
        true
    );
}