<?
class WordPressDb {



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