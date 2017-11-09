<?
if(!defined('ABSPATH')) exit;

class WordPress {
  function query(){}

  function get_col(){}

  function update(){}

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

  function getResults($wpdb)
  {
    return $wpdb->get_results('
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

  function getFields($id)
  {
    return get_fields($id);
  }
}

function wordp() {
  global $wpc;

  if(!isset($wpc)) {
    $wpc= new WordPress();
  }
  return $wpc;
}

// Initialize
wordp();