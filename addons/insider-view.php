<?

// Exit if accessed directly
if(!defined('ABSPATH')) exit;

add_action('admin_menu', 'vk_analytics_admin_page_copy');
require_once(__DIR__. '/mockClasses/mailingsHelpers.php');

function vk_analytics_admin_page_copy() {
    add_menu_page(
        'Insider View Page',
        'Insider View',
        'manage_options',
        'vk-insider-view',
        'vk_insider_view_admin_page_render',
        'dashicons-chart-bar',
        7
    );
}
// 'Distributions Page',
//         'Distributions',
//         'manage_options',
//         'vk-distributions',
//         'vk_distributions_admin_page_render',
//         'dashicons-chart-bar',
//         7

function vk_insider_view_admin_page_render() {
    global $vk_mailings, $mh;

    // Calculate distributions
    $distributions = $vk_mailings->each_mailing_distribution(0, 0);
    foreach ($distributions['campaigns'] as &$campaign) {
      unset($campaign['fields']);
    }

    $limit_per_day = get_option('subscribed_users') / 7;

    foreach ($distributions['campaigns'] as &$campaign) {
      $id = $campaign['id'];
      //$fields = $campaign['fields'];
      // $url = get_permalink($id);
      $limit_per_campaign = round($campaign['share'] * $limit_per_day);
      $campaign['num_fresh_ids'] = sizeof($mh->get_fresh_subscribers_for_campaign($id, $limit_per_campaign, 0));
    }

    // Render
    ?>

    <style type='text/css'>
      #campaign_table {
        border-collapse: collapse;
      }

      #campaign_table td, #campaign_table th {
        border: 1px solid gray;
        padding: 2px;
      }

      .campaign_row {
        font-weight: bold;
      }
    </style>

    <p>Num subscribers in VK list: <?= get_option('subscribed_users') ?></p>
    <p>Max limit to mail per day: <?= round($limit_per_day) ?></p>

    <h1>
      Current Campaigns
    </h1>
    <table id='campaign_table'>
      <thead>
        <tr>
          <th>Campaign id</th>
          <th>Name/subject</th>
          <th>Emails sent</th>
          <th>Conversions</th>
          <th>Losses</th>
          <th>Success Rate</th>
          <th>Current share of emails</th>
          <th># users for next mailing</th>
        </tr>
      </thead>
      <tbody>
        <? foreach($distributions['campaigns'] as &$campaign) { ?>
          <tr class='campaign_row'>
            <td><?= $campaign['id'] ?></td>
            <td><?= $campaign['title'] ?></td>
            <td><?= $campaign['sent'] ?></td>
            <td><?= $campaign['conversions'] ?></td>
            <td><?= $campaign['losses'] ?></td>
            <td><?= round($campaign['rate'], 4) ?></td>
            <td><?= round($campaign['share'], 4) ?></td>
            <td><?= $campaign['num_fresh_ids'] ?></td>
          </tr>
          <?
          usort($campaign['subjects'], function($a, $b) { return $a['rate'] == $b['rate'] ? 0 : ($a['rate'] > $b['rate'] ? -1 : 1); });
          foreach($campaign['subjects'] as $subject) { ?>
            <tr>
              <td></td>
              <td><?= $subject['title'] ?></td>
              <td><?= $subject['sent'] ?></td>
              <td><?= $subject['conversions'] ?></td>
              <td><?= $subject['losses'] ?></td>
              <td><?= round($subject['rate'], 4) ?></td>
              <td><?= round($subject['share'], 4) ?></td>
              <td><?= round($campaign['num_fresh_ids'] * $subject['share']) ?></td>
            </tr>
          <? } ?>
        <? } ?>
      </tbody>
    </table>

    <?
}