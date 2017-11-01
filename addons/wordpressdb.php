<?
class WordPressDb {
  function get_results(){
    return array('campaign_id'=>0);
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