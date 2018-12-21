<?

// Exit if accessed directly
if(!defined('ABSPATH')) exit;
require_once(__DIR__. '/../constants.php');
// Register post type
add_action( 'init', 'register_post_type_campaign' );
function register_post_type_campaign() {
    $labels = array(
        'name'               => _x( 'Campaigns', 'post type general name', 'campaign-textdomain' ),
        'singular_name'      => _x( 'Campaign', 'post type singular name', 'campaign-textdomain' ),
        'menu_name'          => _x( 'Campaigns', 'admin menu', 'campaign-textdomain' ),
        'name_admin_bar'     => _x( 'Campaign', 'add new on admin bar', 'campaign-textdomain' ),
        'add_new'            => _x( 'Add New', 'campaign', 'campaign-textdomain' ),
        'add_new_item'       => __( 'Add New Campaign', 'campaign-textdomain' ),
        'new_item'           => __( 'New Campaign', 'campaign-textdomain' ),
        'edit_item'          => __( 'Edit Campaign', 'campaign-textdomain' ),
        'view_item'          => __( 'View Campaign', 'campaign-textdomain' ),
        'all_items'          => __( 'All Campaigns', 'campaign-textdomain' ),
        'search_items'       => __( 'Search Campaigns', 'campaign-textdomain' ),
        'parent_item_colon'  => __( 'Parent Campaigns:', 'campaign-textdomain' ),
        'not_found'          => __( 'No campaigns found.', 'campaign-textdomain' ),
        'not_found_in_trash' => __( 'No campaigns found in Trash.', 'campaign-textdomain' )
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __( 'Description.', 'campaign-textdomain' ),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'c' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-megaphone',
        'supports'           => array( 'title', 'author' )
    );

    register_post_type('campaign', $args);
}

// Before loading campaign to edit, pull up to date email From lines from ActionKit
function acf_load_from_line_choices( $field ) {
    global $ak;

    // reset choices
    $field['choices'] = array();

    $response = $ak->request(array(
        'path' => "fromline",
        'method' => 'get',
        'data' => array('_limit' => 50)
    ));
    $from_lines = json_decode($response['body']);
    $from_lines = $from_lines->objects;
    foreach ($from_lines as $from_line) {
        $field['choices'][$from_line->id] = htmlentities($from_line->from_line);
    }

    // return the field
    return $field;
}
add_filter('acf/load_field/name=from_line', 'acf_load_from_line_choices');


// Add link to ActionKit campaign from edit page of VictoryKit campaign
add_action('add_meta_boxes', 'add_link_to_action_kit_campaign_meta_box');
function add_link_to_action_kit_campaign_meta_box() {
  add_meta_box('action-kit-meta-box', 'ActionKit', 'display_link_to_action_kit_campaign', 'campaign', 'side', 'low');
}
function display_link_to_action_kit_campaign($post, $metabox) {
  $display = '';
  $ak_page_id = get_post_meta($post->ID, 'ak_page_id', true);
  if ($ak_page_id) {
    $display = "<a href='https://act.demandprogress.org/admin/core/petitionpage/$ak_page_id' target='__blank'>View associated ActionKit Campaign</a>";
  }
  echo $display;
}

// After saving campaign
add_action('save_post_campaign', 'after_saving_campaign', 10, 3);
function after_saving_campaign($post_id, $post, $update) {
    // Ignore drafts
    if ($post->post_status != 'publish') {
        return;
    }

    /*
       Sync with ActionKit...
    */

    global $ak;
    $permalink = get_permalink($post_id);

    $ak_page_id = get_post_meta($post_id, 'ak_page_id', true);
    if ($ak_page_id) {
      $ak_form_id = get_post_meta($post_id, 'ak_form_id', true);
      $ak_page_short_name = get_post_meta($post_id, 'ak_page_short_name', true);
    } else {
      $ak_page_short_name = 'vk-' . time();
    }

    // Create or update petition page
    $response = $ak->request(array(
        'path' => 'petitionpage/' . ($ak_page_id ? $ak_page_id : ''),
        'method' => $ak_page_id ? 'put' : 'post',
        'id' => $update ? $ak_page_id : '',
        'data' => array(
            'fields' => array(
                'description' => 'Sample description',
                'facebook_image_url' => 'https://s3.amazonaws.com/demandprogress/images/add-your-name.png',
            ),
            'list' => '/rest/v1/list/'.VK_LIST_ID.'/', // VictoryKit
            'title' => '(VK) ' . $post->post_title,
            'name' => $ak_page_short_name,
            'tags' => array(
                '/rest/v1/tag/43/',
            )
        )
    ));

    if ($response['error']) {
        return;
    }

    // If creating a new page save the ActionKit data in the database
    if (!$ak_page_id) {
        $ak_page_id = $ak->get_resource_id($response);

        update_post_meta($post_id, 'ak_page_id', $ak_page_id);
        update_post_meta($post_id, 'ak_page_short_name', $ak_page_short_name);
    }

    // Petition form
    $response = $ak->request(array(
        'path' => 'petitionform/' . ($ak_form_id ? $ak_form_id : ''),
        'method' => $ak_form_id ? 'put' : 'post',
        'data' => array(
            'page' => "/rest/v1/petitionpage/$ak_page_id/",
            'statement_text' => '(VK) ' . $post->post_title,
            'thank_you_text' => '<p>Thanks!</p>',
            'client_hosted' => true,
            'client_url' => $permalink
        ),
    ));

    if ($response['error']) {
        return;
    }

    // If creating form for the first time
    if (!isset($ak_form_id) || !$ak_form_id) {
      // Save form data
      $ak_form_id = $ak->get_resource_id($response);
      update_post_meta($post_id, 'ak_form_id', $ak_form_id);

       // Create fields
      $ak->request(array(
          'path' => 'userformfield',
          'method' => 'post',
          'data' => array(
              'field_name' => 'name',
              'form_id' => $ak_form_id,
              'form_type' => 19,
              'input' => 'text',
              'ordering' => 1,
              'status' => 'visible',
              'type' => 'user',
          ),
      ));
      $ak->request(array(
          'path' => 'userformfield',
          'method' => 'post',
          'data' => array(
              'field_name' => 'email',
              'form_id' => $ak_form_id,
              'form_type' => 19,
              'input' => 'text',
              'ordering' => 2,
              'status' => 'required',
              'type' => 'user',
          ),
      ));
      $ak->request(array(
          'path' => 'userformfield',
          'method' => 'post',
          'data' => array(
              'field_name' => 'zip/postal',
              'form_id' => $ak_form_id,
              'form_type' => 19,
              'input' => 'text',
              'ordering' => 3,
              'status' => 'required',
              'type' => 'user',
          ),
      ));
    }

    // Petition follow up (thank you) page and email
    $ak_followup_id = get_post_meta($post_id, 'ak_followup_id', true);
    $from_line = get_post_meta($post_id, 'from_line', true);
    $followup_email_body = get_post_meta($post_id, 'after_action_email_content', true);
    $email_subject = "Re: " . strip_tags(get_post_meta($post_id, 'petition_headline', true));

    // Setup content of "sample email you can send your friends"
    $petition_body = get_post_meta($post->ID, 'body', true);
    $petition_body = strip_tags(html_entity_decode($petition_body, ENT_QUOTES, 'UTF-8')); // Strip out HTML tags and convert HTML entities into ASCII for plain text email body
    $permalink = get_post_permalink($post->ID);
    $email_sharing_body = "I just signed this important petition, and I hope you will too: \n\n ----------- \n\n" . $petition_body . "\n\n ----------- \n\n Could you sign too? \n\n $permalink";

    $response = $ak->request(array(
        'path' => 'pagefollowup/' . ($ak_followup_id ? $ak_followup_id : ''),
        'method' => $ak_followup_id ? 'put' : 'post',
        'data' => array(
            'page' => "/rest/v1/petitionpage/$ak_page_id/",
            'url' => "$permalink?phase=thanks",
            'send_email' => true,
            'email_from_line' => "/rest/v1/fromline/$from_line/",
            'email_wrapper' => 27, // default after action wrapper in ActionKit
            'email_subject' => $email_subject,
            'email_body' => $followup_email_body,
            'taf_body' => $email_sharing_body
        ),
    ));

$mailingsPrint = trim(preg_replace('/\s+/', ' ',var_export( $response, true)));
error_log('$$$response line 231 '.$mailingsPrint);

    if ($response['error']) {
        return;
    }

    if (!$ak_followup_id) {
        $ak_followup_id = $ak->get_resource_id($response);
        update_post_meta($post_id, 'ak_followup_id', $ak_followup_id);
    }
}

// Campaign admin JavaScript
function post_type_campaign_javascript($hook) {
    if ($hook != 'post.php' && $hook != 'post-new.php') {
        return;
    }

    wp_enqueue_script(
        'vk-admin',
        get_bloginfo('template_directory') . '/addons/admin.js'
    );

    wp_enqueue_style(
        'vk-admin',
        get_bloginfo('template_directory') . '/addons/admin.css'
    );
}
add_action('admin_enqueue_scripts', 'post_type_campaign_javascript');

function post_type_campaign_admin_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key == 'title') {
            $new_columns['performance'] = 'Performance';
        }
    }
    return $new_columns;
}
add_filter('manage_campaign_posts_columns', 'post_type_campaign_admin_columns', 10, 2);

function post_type_campaign_admin_performance_columns($column_name, $post_id) {
    if ($column_name != 'performance') {
        return;
    }

    global $wpdb;
    $results = $wpdb->get_row("
        SELECT
            SUM(vkm.sent) AS sent,
            SUM(vkm.conversions) AS conversions
        FROM
            vk_mailing AS vkm
        WHERE
            vkm.campaign_id = $post_id
    ", ARRAY_A);

    if (!$results['sent']) {
        ?>
        <div class="performance">Pending a send</div>
        <?
        return;
    }

    ?>
    <div class="performance">
        <?= $results['conversions'] ?>
        (<?= round(100 * $results['conversions'] / $results['sent'], 3) ?>%)
        new users
        /
        <?= $results['sent'] ?>
        sends
    </div>
    <?
}
add_action('manage_posts_custom_column', 'post_type_campaign_admin_performance_columns', 10, 2);

function post_type_campaign_admin_js() {
    if ($_SERVER['SCRIPT_NAME'] != '/wp-admin/post.php') {
        return;
    }

    wp_enqueue_script(
        'post-type-campaign-admin',
        get_bloginfo('template_directory') . '/addons/post-type-campaign.js',
        array('jquery')
    );
}
add_action('admin_footer', 'post_type_campaign_admin_js');

function post_type_campaign_admin_css() {
    if ($_SERVER['SCRIPT_NAME'] != '/wp-admin/post.php') {
        return;
    }

    wp_enqueue_style(
        'post-type-campaign-admin',
        get_bloginfo('template_directory') . '/css/post-type-campaign-admin.css'
    );
}
add_action('admin_head', 'post_type_campaign_admin_css');


// Add default image to image type fields in Advanced Custom Fields
// From: https://acfextras.com/default-image-for-image-field/
add_action('acf/render_field_settings/type=image',
           'add_default_value_to_image_field', 20);
function add_default_value_to_image_field($field) {
  $args = array(
    'label' => 'Default Image',
    'instructions' => 'Appears when creating a new post',
    'type' => 'image',
    'name' => 'default_value'
  );
  acf_render_field_setting($field, $args);
}
add_action('admin_enqueue_scripts', 'enqueue_uploader_for_image_default');
function enqueue_uploader_for_image_default() {
  $screen = get_current_screen();
  if ($screen && $screen->id && ($screen->id == 'acf-field-group')) {
    acf_enqueue_uploader();
  }
}
add_filter('acf/load_value/type=image', 'reset_default_image', 10, 3);
function reset_default_image($value, $post_id, $field) {
  if (!$value) {
    $value = $field['default_value'];
  }
  return $value;
}
