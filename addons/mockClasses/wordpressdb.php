<?
class WordPressDb {
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