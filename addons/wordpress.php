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

   function calculate_shares($campaigns, $overall, $boost){
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