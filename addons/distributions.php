<?

// Exit if accessed directly
if(!defined('ABSPATH')) exit;

add_action('admin_menu', 'vk_distributions_admin_page');

function vk_distributions_admin_page() {
    add_menu_page(
        'Distributions Page',
        'Distributions',
        'manage_options',
        'vk-distributions',
        'vk_distributions_admin_page_render',
        'dashicons-chart-bar',
        7
    );
}

function vk_distributions_admin_page_render() {
    global $vk_mailings;

    // Scripts
    wp_enqueue_script(
        'canvasjs',
        get_template_directory_uri(). '/vendor/canvasjs/canvasjs.min.js'
    );
    wp_enqueue_script(
        'distributions',
        get_template_directory_uri(). '/addons/distributions.js'
    );

    // Calculate distributions
    $distributions = $vk_mailings->get_distributions(0, 0);
    foreach ($distributions['campaigns'] as &$campaign) {
        unset($campaign['fields']);
    }

    // Render
    ?>
    <script>
        var distributions = <?= json_encode($distributions) ?>;
    </script>
    <h1>
        Distributions
    </h1>

    <style>
    .charts {
        opacity: 0;
        transition: 0.16s ease-out;
    }
    .charts.visible {
        opacity: 1;
    }
    .charts .chart {
        margin-bottom: 42px;
    }
    .canvasjs-chart-credit {
        /* Sorry */
        display: none;
    }
    .data-dump {
        display: none;
    }
    </style>

    <div class="charts"></div>

    <pre class="data-dump"><?= var_dump($distributions) ?></pre>


    <?
}
