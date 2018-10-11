<?

class MailingsHelpers {
    function wp_query_posts(){
       return new WP_Query(array(
            'post_type' => 'campaign',
            'post_status' => 'publish',
        ));
    }

    function getFields($id, $mh_mock){
        if($mh_mock){
            print('in side get fields');
        } else {
           return get_fields($id);
        }
    }

    function setUpCampaigns($results, $campaigns, $mh_mock){
        print('inside setUpCampaigns func');
        $campaigns = $campaigns;
        foreach ($results->posts as $campaign) {
            $id = $campaign->ID;
            $campaigns[$id] = array(
                'conversions' => 0,
                'fields' => $this->getFields($id, $mh_mock),
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

    function get_mailings_results_wpdb($db){
        print('inside get mailings result wpdb');
        return $db->get_results('
            SELECT
                vkm.campaign_id,
                vkm.variation_subject,
                SUM(vkm.conversions) as conversions,
                SUM(vkm.losses) as losses,
                SUM(vkm.sent) as sent
            FROM
                vk_mailing AS vkm
            GROUP BY
                vkm.campaign_id, vkm.variation_subject
        ', ARRAY_A);
    }
//   function mailingStats($mailings, $campaigns, $overall){
//     $results = array();

//       foreach ($mailings as $mailing) {
//           $id = $mailing['campaign_id'];

//           // Make sure to only include currently published campaigns in overall data
//           // TODO: why not just check for campaign published in query above?
//           if (!isset($campaigns[$id])) {
//               continue;
//           }

//           $overall['conversions'] += $mailing['conversions'];
//           $overall['losses'] += $mailing['losses'];
//           $overall['sent'] += $mailing['sent'];

//           $campaigns[$id]['conversions'] += $mailing['conversions'];
//           $campaigns[$id]['losses'] += $mailing['losses'];
//           $campaigns[$id]['sent'] += $mailing['sent'];

//           $subject = $mailing['variation_subject'];
//           $campaigns[$id]['subjects'][$subject] = array(
//               'conversions' => +$mailing['conversions'],
//               'losses' => +$mailing['losses'],
//               'sent' => +$mailing['sent'],
//           );
//       }
//       $results['campaign_result'] = $campaigns;
//       $results['overall_result'] = $overall;
//       return $results;
//    }

 
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