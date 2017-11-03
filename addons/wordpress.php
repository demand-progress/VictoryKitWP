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

  function mailingStats($mailings, $campaigns, $overall){
    $results = array();

      foreach ($mailings as $mailing) {
          $id = $mailing['campaign_id'];

          // Make sure to only include currently published campaigns in overall data
          // TODO: why not just check for campaign published in query above?
          if (!isset($campaigns[$id])) {
              continue;
          }

          $overall['conversions'] += $mailing['conversions'];
          $overall['losses'] += $mailing['losses'];
          $overall['sent'] += $mailing['sent'];

          $campaigns[$id]['conversions'] += $mailing['conversions'];
          $campaigns[$id]['losses'] += $mailing['losses'];
          $campaigns[$id]['sent'] += $mailing['sent'];

          $subject = $mailing['variation_subject'];
          $campaigns[$id]['subjects'][$subject] = array(
              'conversions' => +$mailing['conversions'],
              'losses' => +$mailing['losses'],
              'sent' => +$mailing['sent'],
          );
      }
      $results['campaign_result'] = $campaigns;
      $results['overall_result'] = $overall;
      return $results;
   }

   function boost($overall, $boost){
     $results = array();
     $overall['boost'] = $boost;

     // Overall rate
     $overall['rate'] = ($overall['conversions'] - $overall['losses'] + $boost) / ($overall['sent'] + $boost);
     if ($overall['rate'] < 0) {
         $overall['rate'] = 0; // TODO: why would we not track negative results?
     }

      $results['boost_value'] = $boost;
      $results['overall_value'] = $overall;

      return $results;
   }

   function calculate_shares($campaigns){
     $results = array();
     $campaign_rate_sum = 0;
     foreach ($campaigns as $campaign_index => &$campaign) {
         // Subjects
         $fields = $campaign['fields'];
         $subject_rate_sum = 0;
         $valid_subjects = 0;

         foreach ($campaign['subjects'] as $subject_index => &$subject) {
             $enabled = $fields['subjects'][$subject_index]['enabled'];
             $subject['title'] = $fields['subjects'][$subject_index]['subject'];
             if (!$enabled) {
                 $subject['rate'] = 0;
                 continue;
             }

             $valid_subjects++;

             $rate = (
                 ($subject['conversions'] - $subject['losses'] + $boost * $overall['rate'])
                 /
                 ($subject['sent'] + $boost)
             );

             if ($rate < 0) {
                 $subject['rate'] = 0; // TODO: why not track negative rates?
                 continue;
             }

             $subject['rate'] = $rate;
             $subject_rate_sum += $rate; // TODO: why not track negative rates?
         }

         // If no enabled subjects skip this campaign
         if ($valid_subjects == 0) {
             $campaign['valid'] = false;
             continue;
         }

         foreach ($campaign['subjects'] as $subject_index => &$subject) {
             $share = $subject_rate_sum ? $subject['rate'] / $subject_rate_sum : 0;
             $subject['share'] = $share;
         }

         // Campaign
         $rate = (
             ($campaign['conversions'] - $campaign['losses'] + $boost * $overall['rate'])
             /
             ($campaign['sent'] + $boost)
         );

         if ($rate < 0) {
             $campaign['rate'] = 0; // TODO: why not track negative rates?
             continue;
         }

         $campaign['rate'] = $rate;
         $campaign_rate_sum += $rate;
     }
      $results['campaigns_values'] = $campaigns;
      return $results;
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