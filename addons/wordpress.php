<?
require_once(__DIR__. '/wordpressdb.php');


class WordPress {

function getOptions($param)
  {
    return get_option($param);
  }

function wordPressQuery()
  {
    return new WP_Query(array(
        'post_type' => 'campaign',
        'post_status' => 'publish',
    ));
  }



function loopActiveCampaigns($results)
 {
   global $wpdb;

    $campaigns = array();
    foreach ($results->posts as $campaign) {
        $id = $campaign->ID;
        $campaigns[$id] = array(
            'conversions' => 0,
            'fields' => $wpdb->getFields($id),
            'id' => $id,
            'losses' => 0,
            'sent' => 0,
            'subjects' => array(),
            'title' => $campaign->post_title,
            'valid' => true,
        );
        // Get all subjects
        $subjects = $campaigns[$id]['fields']['subjects'];
        for ($i = 0; $i < count($subjects); $i++) {
            $campaigns[$id]['subjects'][$i] = array(
                'conversions' => 0,
                'losses' => 0,
                'sent' => 0,
            );
         }
      }
     return $campaigns;
  }
}

function wp() {
    global $wp;

    if(!isset($wp)) {
        $wp = new WordPress();
    }

    return $wp;
}

// Initialize
wp();