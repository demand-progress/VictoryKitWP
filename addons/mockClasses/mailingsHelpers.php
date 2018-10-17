<?

class MailingsHelpers {
    function wp_query_posts($mh_mock){
       return new WP_Query(array(
            'post_type' => 'campaign',
            'post_status' => 'publish',
        ));
    }

    function getFields($id, $mh_mock){
        if($mh_mock){
            return array('subjects' => array());
        } else {
           return get_fields($id);
        }
    }

    function setUpCampaigns($results, $campaigns, $mh_mock){
       
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

     // Get all subscribers who have not been mailed for this campaign AND have not been mailed for any campaign within the last week
     function get_fresh_subscribers_for_campaign($campaign_id, $limit, $wpdb_mock)
     {
         global $wpdb;
 
         if($wpdb_mock){
            $wpdb = $wpdb_mock;
         }
 
         $sql = "
         SELECT
             vks.ak_user_id
         FROM
             vk_subscriber AS vks
         LEFT JOIN
             vk_subscriber_mailing AS vksm
         ON
             vks.ak_user_id = vksm.ak_user_id AND
             (
                 vksm.campaign_id = $campaign_id
                 OR
                 vksm.created_at > DATE_SUB(NOW(), INTERVAL 1 WEEK)
             )
         WHERE
             vksm.ak_user_id IS NULL
         LIMIT $limit;
         ";
 
         // $allsql = trim(preg_replace('/\s+/', ' ',var_export( $sql, true)));
         // error_log('line 260 sql query '.$allsql);
                 
         $response = $wpdb->get_col($sql, 0);
                
         $ids = array_map(function($el) {
             return +$el;
         }, $response);
        
         return $ids;
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