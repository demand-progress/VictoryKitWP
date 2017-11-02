<?
class WordPressDb {

  function getResults($wpdb){
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

  function getFields($id){
    return get_fields($id);
  }
}

function wpdb() {
    global $wpdb;

    if(!isset($wpdb)) {
        $wpdb = new WordPressDb();
    }

    return $wpdb;
}

// Initialize
wpdb();