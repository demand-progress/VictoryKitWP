<?
// Load WP
if (!isset($wp_did_header)) {
    $wp_did_header = true;
    require_once(dirname(__FILE__) . '../../../../../wp-load.php');
    wp();
    require_once(ABSPATH . WPINC . '/template-loader.php');
}

// Authenticate (Admins-only)
if (!current_user_can('edit_theme_options')) {
    header('Location: /');
    die;
}

// Operation
$operation = '';
if (isset($_GET['operation'])) {
    $operation = $_GET['operation'];
}

// Callbacks
$callbacks = array(
    'get-subject-performance' => function() {
        global $wpdb;

        $post_id = $_GET['post_id'];
        $sql = $wpdb->prepare('
        SELECT
            vkm.variation_subject,
            SUM(vkm.sent) AS sent,
            SUM(vkm.conversions) AS conversions
        FROM
            vk_mailing AS vkm
        WHERE
            vkm.campaign_id = %d
        GROUP BY
            vkm.variation_subject
        ', $post_id);
        $results = $wpdb->get_results($sql, ARRAY_A);
        echo json_encode($results);
    },
    
    'get-sharing-performance' => function() {
        global $vk_sharing;

        $performance = $vk_sharing->get_sharing_performance(array(
            'post_id' => $_GET['post_id'],
        ));
        echo json_encode($performance);
    },
);

if (isset($callbacks[$operation])) {
    $callbacks[$operation]();
} else {
    echo 'Operation not found...';
}
