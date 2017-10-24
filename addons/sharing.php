<?

// Exit if accessed directly
if(!defined('ABSPATH')) exit;

class Sharing {
    function __construct() {
        // ...
    }

    function queryResponse($sql){
      global $ak;
      return $ak->query($sql, true);
    }

    function get_sharing_stats_from_ak($post_id) {


        // Request sources from AK
        $ak_page_id = get_post_meta($post_id, 'ak_page_id', true);
        $sql = "
            SELECT
                COUNT(tbl.id) AS amount,
                tbl.source
            FROM
                core_action AS tbl
            WHERE
                tbl.page_id = $ak_page_id AND
                tbl.subscribed_user = 1 AND
                tbl.source LIKE 'vk&%'
            GROUP BY
                tbl.source
        ";
        $response = queryResponse($sql);

        // Initialize each variant with a count of zero
        $variants = array(
            'sd' => array(),
            'si' => array(),
            'st' => array(),
        );
        $fields = get_fields($post_id);
        if (isset($fields['share_descriptions']) && $fields['share_descriptions']) {
            foreach ($fields['share_descriptions'] as $index => $value) {
                $variants['sd'][$index] = 0;
            }
        }
        if (isset($fields['share_images']) && $fields['share_images']) {
            foreach ($fields['share_images'] as $index => $value) {
                $variants['si'][$index] = 0;
            }
        }
        if (isset($fields['share_titles']) && $fields['share_titles']) {
            foreach ($fields['share_titles'] as $index => $value) {
                $variants['st'][$index] = 0;
            }
        }

        // Parse sources
        foreach ($response['data'] as $row) {
            $parts1 = preg_split('%&%', $row['source']);
            foreach ($parts1 as $parts2) {
                $parts3 = preg_split('%=%', $parts2);
                $type = $parts3[0];
                if (!isset($variants[$type])) {
                    continue;
                }
                $variant = $parts3[1];
                if (!isset($variants[$type][$variant])) {
                    $variants[$type][$variant] = 0;
                }
                $variants[$type][$variant] += $row['amount'];
            }
        }

        return $variants;
    }

    function get_sharing_performance($params) {
        global $wpdb;

        $post_id = $params['post_id'];
        $sql = $wpdb->prepare('
        SELECT
            tbl.type,
            tbl.variant,
            tbl.views,
            tbl.conversions
        FROM
            vk_share_fb_conversion AS tbl
        WHERE
            tbl.campaign_id = %d
        ', $post_id);
        $results = $wpdb->get_results($sql, ARRAY_A);

        $performance = array(
            'share_descriptions' => array(),
            'share_images' => array(),
            'share_titles' => array(),
        );

        $column_name_map = array(
            'sd' => 'share_descriptions',
            'si' => 'share_images',
            'st' => 'share_titles',
        );

        foreach ($results as $variant) {
            $type = $column_name_map[$variant['type']];
            $index = $variant['variant'];
            $performance[$type][$index] = array(
                'views' => +$variant['views'],
                'conversions' => +$variant['conversions'],
            );
        }

        return $performance;
    }
}

/******** Actions *******/

// Update sharing stats
//   Run every hour
function vk_sharing_update_sharing_stats_action() {
    global $vk_sharing, $wpdb;

    $results = new WP_Query(array(
        'post_type' => 'campaign',
        'post_status' => 'publish',
    ));

    $column_name_map = array(
        'sd' => 'description',
        'si' => 'image',
        'st' => 'title',
    );

    foreach ($results->posts as $post) {
        $campaign_id = $post->ID;
        $stats = $vk_sharing->get_sharing_stats_from_ak($campaign_id);
        foreach ($stats as $type => $variants) {
            $column = $column_name_map[$type];
            foreach ($variants as $variant => $amount) {
                $views = $wpdb->get_var("
                    SELECT
                        COUNT(*) AS amount
                    FROM
                        vk_share_fb_view AS tbl
                    WHERE
                        tbl.campaign_id = $campaign_id AND
                        tbl.$column = $variant
                ");
                $wpdb->replace(
                    'vk_share_fb_conversion',
                    array(
                        'campaign_id' => $campaign_id,
                        'type' => $type,
                        'variant' => $variant,
                        'views' => $views,
                        'conversions' => $amount,
                    )
                );
            }
        }
    }
}
add_action('vk_sharing_update_sharing_stats', 'vk_sharing_update_sharing_stats_action');


// Initialize
function create_sharing_instance() {
    global $vk_sharing;

    if(!isset($vk_sharing)) {
        $vk_sharing = new sharing();
    }

    return $vk_sharing;
}
create_sharing_instance();
