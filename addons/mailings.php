<?

// Exit if accessed directly
if(!defined('ABSPATH')) exit;

// Display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Mailings {
    function __construct()
    {
        // ...
    }

    function get_distributions()
    {
        global $wpdb;

        if (!get_option('subscribed_users')) {
          // no subscribed users in DB yet
          return array('campaigns' => array(), 'overall' => array());
        }

        // Get active campaigns
        //pulling out campaigns from wp post what have post_type set as campaign and post_status set as publish
        $campaigns = array();
        $results = new WP_Query(array(
            'post_type' => 'campaign',
            'post_status' => 'publish',
        ));
    
        foreach ($results->posts as $campaign) {
            $id = $campaign->ID;
            $campaigns[$id] = array(
                'conversions' => 0,
                'fields' => get_fields($id),
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
        // echo '<pre>' . var_export( $campaigns, true) . '</pre>';

        // Get campaign performance
        $overall = array(
            'conversions' => 0,
            'losses' => 0,
            'sent' => 0,
        );
        $mailings = $wpdb->get_results('
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

        // This allows brand new campaigns to have a chance to succeed.
        // It probably is not needed if we are going to be sending each new campaign to more than several hundred people,
        // but it helps for testing with smaller amounts of people because it basically starts off the campaign at the same
        // rate as the overall campaign success rate and slightly adjusts from there
        $boost = 500.0;
        $overall['boost'] = $boost;

        // Overall rate
        $overall['rate'] = ($overall['conversions'] - $overall['losses'] + $boost) / ($overall['sent'] + $boost);
        if ($overall['rate'] < 0) {
            $overall['rate'] = 0; // TODO: why would we not track negative results?
        }

        // Calculate shares
        $campaign_rate_sum = 0;
           
        // echo '<pre>' . var_export($campaign, true) . '</pre>';

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
            $fresh_ids = count($this->get_fresh_subscribers_for_campaign($campaign['id'], $limit_per_campaign));

            // Plenty? Great.
            if ($fresh_ids >= $limit_per_campaign) {
                $campaign['limit'] = $limit_per_campaign;
                continue;
            }

            // Shortage of fresh IDs. Calculations are required.
            $campaign['limit'] = $fresh_ids;
            $share = $fresh_ids / $limit_per_day;
            $campaign_share_sum += $share - $campaign['share'];
            $campaign['share'] = $share;
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

    // Get all subscribers who have not been mailed for this campaign AND have not been mailed for any campaign within the last week
    function get_fresh_subscribers_for_campaign($campaign_id, $limit)
    {
        global $wpdb;

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
        $response = $wpdb->get_col($sql, 0);
        $ids = array_map(function($el) {
            return +$el;
        }, $response);
        return $ids;
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

    function render($params)
    {
        $html = file_get_contents(dirname(__FILE__) . '/email-template.html');
        $html = str_replace('$body',                  $params['body'],                  $html); // Turn newlines into <brs> in body
        $html = str_replace('$petition_headline',     $params['petition_headline'],     $html);
        $html = str_replace('$salutation',            $params['salutation'],            $html);
        $html = str_replace('$url',                   $params['url'],                   $html);

        if (!isset($params['wrap'])) {
            return $html;
        }

        $wrapper = file_get_contents(dirname(__FILE__) . '/email-template-wrapper.html');
        $html = str_replace('$content', $html, $wrapper);

        return $html;
    }

    function send($params)
    {
        // Verify targeting
        if (
            !$params['subscribers']
            ||
            !is_array($params['subscribers'])
            ||
            count($params['subscribers']) < 1
        ) {
            return;
        }

        // Render
        $html = $this->render($params);

        global $ak;
      
        $response = $ak->request(array(
            'path' => 'mailer',
            'method' => 'post',
            'data' => array(
                'fromline' => "/rest/v1/fromline/{$params['from_line']}/",
                'subjects' => array($params['subject']),
                'notes' => 'Generated by VictoryKit',
                'emailwrapper' => 27, // Demand Progress wrapper
                'includes' => array(
                    'lists' => array(25), // VK list. TODO: store this as a constant somewhere
                    'users' => $params['subscribers'], // Subscribers
                ),
                'limit' => $params['limit'], // Limit users per mailing
                // 'excludes' => array(
                //     'mailings' => $mailings, // Array of mailing IDs, for avoiding multiple sends
                // ),
                'tags' => array('victorykit'),
                'html' => $html,
                'sort_by' => 'random',
            ),
        ));

        $location = $response['headers']->getAll()['location'];
        preg_match('%/(\d+)/$%', $location, $matches);
        $ak_mailing_id = +$matches[1];

        $response = $ak->request(array(
            'path' => "mailer/$ak_mailing_id/rebuild",
            'method' => 'post',
            'data' => array(),
        ));

        // '<pre>' . var_export($response , true) . '</pre>';


        $response['ak_mailing_id'] = $ak_mailing_id;

        global $wpdb;
        $wpdb->insert('vk_mailing', array(
            'ak_mailing_id' => $ak_mailing_id,
            'campaign_id' => $params['campaign_id'],
            'status' => 'rebuilding',
            'variation_subject' => $params['variation_subject'],
        ));

        return $response;
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
        cs.list_id = 25
    ";
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
        cs.list_id = 25
    ';
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
function vk_mailings_create_new_mailings_action() {
    global $vk_mailings, $wpdb;

    $limit_per_day = get_option('subscribed_users') / 7;
    
    $distributions = $vk_mailings->get_distributions();
    $allCampaigns = trim(preg_replace('/\s\s+/', ' ', var_export($distributions, true)));
 
    error_log('#distributionResults vk_mailings_create_new_mailings_action: '.$allCampaigns);

    foreach ($distributions['campaigns'] as $campaign) {
        $id = $campaign['id'];
        $fields = $campaign['fields'];
        $url = get_permalink($id);
        $limit_per_campaign = round($campaign['share'] * $limit_per_day);
        $fresh_ids = $vk_mailings->get_fresh_subscribers_for_campaign($id, $limit_per_campaign);

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

            $response = $vk_mailings->send($params);

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

$vkInstance = create_mailings_instance();

// $vkInstance->vk_mailings_create_new_mailings_action();
// vk_mailings_create_new_mailings_action();
