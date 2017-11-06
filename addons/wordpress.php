<?
require_once(__DIR__. '/wordpressdb.php');


class WordPress {

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
}

function wp() {
    global $wp;

    if(!isset($wp)) {
        $wp = new WordPress();
    }

    return $wp;
}

// Initialize
wp();