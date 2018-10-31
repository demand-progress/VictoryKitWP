<?

// Exit if accessed directly
if(!defined('ABSPATH')) exit;
require_once(__DIR__. '/../constants.php');
require_once(__DIR__. '/mockClasses/mailingsHelpers.php');
// Display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Mailings {
    function __construct()
    {
        // ...
    }

    function get_distributions($wpdb_mock, $mh_mock)
    {
        global $wpdb;
        global $mh;
   
        if($wpdb_mock){
            $wpdb = $wpdb_mock;
            $mh = $mh_mock; 
        } 

        if (!get_option('subscribed_users')) {
          // no subscribed users in DB yet
          return array('campaigns' => array(), 'overall' => array());
        }

        // Get active campaigns
        $campaigns = array();

        //**********investigate what comes back from wp_query_posts
        $results = $mh->wp_query_posts($mh_mock);
       
        // $results1 = trim(preg_replace('/\s+/', ' ',var_export( $results, true)));
        // var_dump($results1);

        // var_dump($mh);
        $campaigns = $mh->setUpCampaigns($results, $campaigns, $mh_mock);

        // Get campaign performance
        $overall = array(
            'conversions' => 0,
            'losses' => 0,
            'sent' => 0,
        );

        $mailings = $mh->get_mailings_results_wpdb($wpdb);

    //    need to print error log here
    //    $mailingsPrint = trim(preg_replace('/\s+/', ' ',var_export( $mailings, true)));
    //    error_log('$$$line 54 return value of get_results '.$mailingsPrint);
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
        // $campaignsPrint = trim(preg_replace('/\s+/', ' ',var_export( $campaigns, true)));
        // error_log('$$$line 84 value of campaigns after loop '.$campaignsPrint);

        // This allows brand new campaigns to have a chance to succeed.
        // It probably is not needed if we are going to be sending each new campaign to more than several hundred people,
        // but it helps for testing with smaller amounts of people because it basically starts off the campaign at the same
        // rate as the overall campaign success rate and slightly adjusts from there
        $boost = BOOST;
        $overall['boost'] = $boost;

        // Overall rate
        $overall['rate'] = ($overall['conversions'] - $overall['losses'] + $boost) / ($overall['sent'] + $boost);
        
        // $camp = trim(preg_replace('/\s+/', ' ',var_export( $campaigns, true)));
        // error_log('line 199 campaigns setting stats overall with boost '.$camp);
        // $arrayThing = ARRAY_A;
        // $aThing = trim(preg_replace('/\s+/', ' ',var_export( $arrayThing, true)));
        // error_log('line 80 Array_A value '.$aThing);    
        
        if ($overall['rate'] < 0) {
            $overall['rate'] = 0; // TODO: why would we not track negative results?
        }

        // Calculate shares
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

        // Filter out invalid campaigns
        $campaigns = array_filter($campaigns, function($campaign) {
            return $campaign['valid'];
        });

        // Get share percentages
        foreach ($campaigns as $campaign_index => &$campaign) {
            $share = $campaign_rate_sum ? $campaign['rate'] / $campaign_rate_sum : 0;
            $campaign['share'] = $share;
        }

        // Limit share percentages, based on subscriber availability
        $campaign_share_sum = 1;
        $limit_per_day = get_option('subscribed_users') / 7;
      
        foreach ($campaigns as $campaign_index => &$campaign) {
            
            $limit_per_campaign = round($campaign['share'] * $limit_per_day);
            // $limit = trim(preg_replace('/\s+/', ' ',var_export($limit_per_campaign, true)));
            // error_log('line 192 limit_per_campaign '.$limit);
            $fresh_ids = count($mh->get_fresh_subscribers_for_campaign($campaign['id'], $limit_per_campaign, $wpdb_mock));
            // Plenty? Great.
            //fresh ids should not be 0 
           
            if ($fresh_ids >= $limit_per_campaign) {
                $campaign['limit'] = $limit_per_campaign;
                continue;
            }
            // Shortage of fresh IDs. Calculations are required.
            $campaign['limit'] = $fresh_ids;
            //CURRENTLY RETURNING 0 FOR $SHARE THIS IS MAKING A NAN VALUE
            $share = $fresh_ids / $limit_per_day;
            
            $campaign_share_sum += $share - $campaign['share'];

            $campaign['share'] = $share;
            
            // $campShare = $campaign['share'];
            // $allsql = trim(preg_replace('/\s+/', ' ',var_export( $campShare, true)));
            // error_log('line 225 $campaign_share_sum '.$campaign_share_sum);
            // error_log('line 226 variable campaing[share] '.$share);
        }
        
        foreach ($campaigns as $campaign_index => &$campaign) {
            $campaign['share'] = $campaign['share'] / $campaign_share_sum;
        }
        
        usort($campaigns, function($a, $b) {
            $difference = $b['share'] - $a['share'];
            if ($difference == 0) {
                return 0;
            } else if ($difference > 0) {
                return 1;
            } else {
                return -1;
            }
        });

        return array(
            'campaigns' => $campaigns,
            'overall' => $overall,
        );
    }

   

    // Update conversion stats from ActionKit for a campaign mailing
    function get_mailing_stats_from_ak($ak_mailing_id)
    {
        global $ak;

        $conversions = $ak->query("
            SELECT
                COUNT(ca.id) AS conversions
            FROM
                core_action AS ca
            WHERE
                ca.subscribed_user = 1 AND
                ca.mailing_id = $ak_mailing_id
        ")['data']['conversions'];

        $losses = $ak->query("
            SELECT
                COUNT(ca.id) AS losses
            FROM
                core_action AS ca
            JOIN
                core_unsubscribeaction AS cu
            ON
                ca.id = cu.action_ptr_id
            WHERE
                ca.mailing_id = $ak_mailing_id
        ")['data']['losses'];

        $sent = $ak->query("
            SELECT
                cm.progress AS sent
            FROM
                core_mailing AS cm
            WHERE
                cm.id = $ak_mailing_id
        ")['data']['sent'];

        return array(
            'conversions' => $conversions,
            'losses' => $losses,
            'sent' => $sent,
        );
    }
}

/**************** Actions *****************/

// Update total count of subscribers in VictoryKit list
//   Run every 12 hours
function vk_mailings_update_subscribed_users_count_action() {
    
    global $ak;
    
    $sql = "
    SELECT
        COUNT(DISTINCT cs.id) AS user_count
    FROM
        core_subscription AS cs
    WHERE
        cs.list_id =".VK_LIST_ID;
    $response = $ak->query($sql);

    if ($response['success']) {
        $count = $response['data']['user_count'];
    } else {
        $count = 0;
    }

    update_option('subscribed_users', $count);
}
add_action('vk_mailings_update_subscribed_users_count', 'vk_mailings_update_subscribed_users_count_action');

// Update VictoryKit subscriber database from ActionKit subscriber database
//   Run once a day at 6:15am
//   TODO: Really have to truncate every time? Isnt this going to be big and slow?
function vk_mailings_sync_subscribers_action() {
    // Get all subscribers...
    global $ak, $wpdb;

    // Collect IDs from AK
    $sql = '
    SELECT
        cs.user_id
    FROM
        core_subscription AS cs
    WHERE
        cs.list_id ='.VK_LIST_ID
    ;
    $response = $ak->query($sql, true);
    $ids = array_map(function($el) {
        return +$el['user_id'];
    }, $response['data']);

    // Clear VK table
    $wpdb->query('
        TRUNCATE TABLE vk_subscriber
    ');

    // Insert into VK table
    $id_chunks = array_chunk($ids, 1000);
    foreach ($id_chunks as $ids) {
        $ids = '(' . join('), (', $ids) . ')';
        $sql = "
        INSERT INTO vk_subscriber
        (ak_user_id)
        VALUES
        $ids;
        ";
        $wpdb->query($sql);
    }
}
add_action('vk_mailings_sync_subscribers', 'vk_mailings_sync_subscribers_action');


// For each completed mailing that was created in the last week update the stats on its success
//   Run every hour
function vk_mailings_update_mailing_stats_action() {
    global $vk_mailings, $wpdb;

    $mailings = $wpdb->get_col('
        SELECT
            vkm.ak_mailing_id
        FROM
            vk_mailing AS vkm
        WHERE
            vkm.status="completed"
            AND
            vkm.created_at > DATE_SUB(NOW(), INTERVAL 1 WEEK)
    ');

    foreach ($mailings as $mailing) {
        $stats = $vk_mailings->get_mailing_stats_from_ak($mailing);
        $wpdb->update(
            'vk_mailing',
            array(
                'conversions' => $stats['conversions'],
                'losses' => $stats['losses'],
                'sent' => $stats['sent'],
            ),
            array('ak_mailing_id' => $mailing)
        );
    }
}
add_action('vk_mailings_update_mailing_stats', 'vk_mailings_update_mailing_stats_action');


// Create new mailings based on the currently running campaigns
//   Run once a day at 8am
function vk_mailings_create_new_mailings_action($vk_mailings_mock, $wpdb_mock, $mhMock) {
    global $vk_mailings, $wpdb, $mh;

    if($vk_mailings_mock){
        $vk_mailings = $vk_mailings_mock;
        $wpdb = $wpdb_mock;
        $mh = $mhMock;
    } 

    $limit_per_day = $mh->getoption();
    
    $distributions = $vk_mailings->get_distributions($vk_mailings_mock, $wpdb_mock, $mhMock);
    // $distributions_results = trim(preg_replace('/\s+/', ' ',var_export( $distributions, true)));
    // $distributions_results = sizeof($distributions_results);
    // error_log('@@@ limit_per_day variable '.$distributions_results);
    foreach ($distributions['campaigns'] as $campaign) {
        $id = $campaign['id'];
        $fields = $campaign['fields'];
        $url = $mh->get_url($id);
        //need to print out why $limit_per_campaign NAN -> this is breaking everything
        // $allsql = trim(preg_replace('/\s+/', ' ',var_export( $campaign, true)));
        // error_log('%%%%%%%%%%% ');
        // error_log('line 531 $campaign '.$allsql);
        $shares = $campaign['share'];
        // $sCamp = trim(preg_replace('/\s+/', ' ',var_export( $shares, true)));
        // error_log('line 535 campaign[share] variable '.$sCamp);
        $limit_per_campaign = round($campaign['share'] * $limit_per_day);
       
        $fresh_ids = $mh->get_fresh_subscribers_for_campaign($id, $limit_per_campaign, $wpdb);
 
        // Out of users to send to?
        if (count($fresh_ids) == 0) {
            continue;
        }

        // Send mailing for each enabled subject
        foreach ($campaign['subjects'] as $index => $subject) {
            $share = round($subject['share'] * $limit_per_campaign);
            
            // Skip disabled subjects
            if ($share == 0) {
                continue;
            }
           
            // Claim IDs
            $subscribers = array_splice($fresh_ids, 0, $share);

            // Create mailing
            $params = array(
                'from_line' => $fields['from_line'],
                'body' => str_replace('&#8221;', '"', $fields['body']),
                'petition_headline' => str_replace('&#8221;', '"', $fields['petition_headline']),
                'campaign_id' => $id,
                'limit' => $share,
                'salutation' => str_replace('&#8221;', '"', $fields['salutation']),
                'subject' => $fields['subjects'][$index]['subject'],
                'subscribers' => $subscribers,
                'url' => $url,
                'variation_subject' => $index,
            );
            // print('######');
            // var_dump($params);
            // $allsql = trim(preg_replace('/\s+/', ' ',var_export( $params, true)));
            // error_log('s line 563: '.$allsql);
            // $sCamp = trim(preg_replace('/\s+/', ' ',var_export( $params, true)));
            // error_log('$$$$$$$$');
            // error_log('line 550 $subject variable '.$sCamp);
            $response = $mh->send($params);
           
            // Save mailing records for VK
            $ak_mailing_id = $response['ak_mailing_id'];
            $subscriber_chunks = array_chunk($subscribers, 1000); // Chunk so SQL queries dont get too big
    
            foreach ($subscriber_chunks as $subscriber_chunk) {
          
                $values = array();
                foreach ($subscriber_chunk as $subscriber) {
                    $values[] = "($subscriber, $ak_mailing_id, $id)";
                }
                $values = join(', ', $values);
                $sql = "
                INSERT INTO vk_subscriber_mailing
                (ak_user_id, ak_mailing_id, campaign_id)
                VALUES
                $values;
                ";
                $wpdb->query($sql);
            }
        }
    }
}
add_action('vk_mailings_create_new_mailings', 'vk_mailings_create_new_mailings_action');


// Check to see if mailings that were rebuilding are ready to send
//   Run once a day at 9am, an hour after mailings are created and should be setup to rebuild
//   TODO: as we have lots of subscribers and campaigns should double check that all mailings get created and rebuilt within an hour and are then ready to send
function vk_mailings_queue_mailings_action() {
    global $ak, $vk_mailings, $wpdb;

    $mailings = $wpdb->get_results('
        SELECT *
        FROM vk_mailing
        WHERE status="rebuilding"
    ', ARRAY_A);

    foreach ($mailings as $mailing) {
        $ak_mailing_id = $mailing['ak_mailing_id'];
        $sql = "
            SELECT
                cm.id,
                cm.expected_send_count > 0 as 'ready',
                cm.query_status,
                cm.hidden
            FROM core_mailing cm
            WHERE
            cm.id = $ak_mailing_id
            ORDER BY id DESC
            LIMIT 1
        ";
        $response = $ak->query($sql);

        $hidden = $response['data']['hidden'];
        if ($hidden) {
            // Disable
            $wpdb->update(
                'vk_mailing',
                array(
                    'status' => 'hidden',
                ),
                array('id' => $mailing['id'])
            );
            continue;
        }

        $ready = $response['data']['ready'];
        if (!$ready) {
            // Skip
            continue;
        }

        // Send!
        $response = $ak->request(array(
            'path' => "mailer/$ak_mailing_id/queue",
            'method' => 'post',
            'data' => array(),
        ));

        $wpdb->update(
            'vk_mailing',
            array(
                'status' => 'queued',
            ),
            array('id' => $mailing['id'])
        );
    }
}
add_action('vk_mailings_queue_mailings', 'vk_mailings_queue_mailings_action');


// Rebuild all mailings that were supposed to be rebuilding but have an invalid status in ActionKit
//   XXX: not being run right now. This existed when mailings referenced past AK mailings in their exclusion queries.
function vk_mailings_rebuild_mailings_action() {
    global $ak, $vk_mailings, $wpdb;

    $mailings = $wpdb->get_results('
        SELECT *
        FROM vk_mailing
        WHERE status="rebuilding"
    ', ARRAY_A);

    foreach ($mailings as $mailing) {
        $ak_mailing_id = $mailing['ak_mailing_id'];
        $sql = "
            SELECT
                cm.id,
                cm.expected_send_count > 0 AS 'ready',
                cm.query_status,
                cm.hidden
            FROM
                core_mailing AS cm
            WHERE
                cm.id = $ak_mailing_id AND
                cm.query_status = 'invalid'
            LIMIT 1
        ";
        $response = $ak->query($sql);

        if (!$response['data']) {
            continue;
        }

        // Hidden
        $hidden = $response['data']['hidden'];
        if ($hidden) {
            // Disable
            $wpdb->update(
                'vk_mailing',
                array(
                    'status' => 'hidden',
                ),
                array('id' => $mailing['id'])
            );
            continue;
        }

        // Needs rebuild
        $ready = $response['data']['ready'];
        if (!$ready) {
            if ($response['data']['query_status'] == 'invalid') {
                $response = $ak->request(array(
                    'path' => "mailer/$ak_mailing_id/rebuild",
                    'method' => 'post',
                    'data' => array(),
                ));
            }
        }
    }
}
add_action('vk_mailings_rebuild_mailings', 'vk_mailings_rebuild_mailings_action');

// Check for any mailings that were queued to send in ActionKit and see if they have been completed. Hide completed mailings
//   Run every 5 minutes. Why would we run this every 5 minutes but the queue mailings one only once per day??
//   TODO: do we really want to hide all completed mailings. I guess that's how we know not to keep checking that mailing in our queries?
function vk_mailings_complete_mailings_action() {
    global $ak, $vk_mailings, $wpdb;

    $mailings = $wpdb->get_results('
        SELECT *
        FROM vk_mailing
        WHERE status="queued"
        -- ORDER BY id DESC
    ', ARRAY_A);

    foreach ($mailings as $mailing) {
        $ak_mailing_id = $mailing['ak_mailing_id'];
        $sql = "
            SELECT
                cm.id
            FROM
                core_mailing AS cm
            WHERE
                cm.id = $ak_mailing_id AND
                cm.status = 'completed'
            LIMIT 1
        ";
        $response = $ak->query($sql);

        if (!$response['data']) {
            continue;
        }

        // echo $ak_mailing_id;
        // die;

        $response = $ak->request(array(
            'path' => "mailing/$ak_mailing_id",
            'method' => 'put',
            'data' => array(
                'hidden' => true,
                'fromline' => NULL,
            ),
        ));

        // Complete
        $wpdb->update(
            'vk_mailing',
            array(
                'status' => 'completed',
            ),
            array('id' => $mailing['id'])
        );
    }
}
add_action('vk_mailings_complete_mailings', 'vk_mailings_complete_mailings_action');


function vk_mailings_cron_test_action() {
    $count = get_option('cron_test', 0);
    update_option('cron_test', $count + 1);
}
add_action('vk_mailings_cron_test', 'vk_mailings_cron_test_action');


// Initialize
function create_mailings_instance() {
    global $vk_mailings;

    if(!isset($vk_mailings)) {
        $vk_mailings = new Mailings();
    }

    return $vk_mailings;
}

create_mailings_instance();

// do_action('vk_mailings_create_new_mailings');
// $allsql = trim(preg_replace('/\s+/', ' ',var_export( $mailings, true)));
// error_log('sql mailing_stats line 468: '.$allsql);