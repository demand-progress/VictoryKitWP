<?

class mailingsHelpers {
  function loopActiveCampaigns($results, $wpdb)
   {
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

function mh() {
    global $mh;

    if(!isset($mh)) {
        $mh = new mailingsHelpers();
    }

    return $mh;
}

// Initialize
mh();