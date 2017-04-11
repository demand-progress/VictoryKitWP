<?

// Exit if accessed directly
if(!defined('ABSPATH')) exit;

// Render dashboard widget
function vk_dashboard_widget() {
    ?>
        <p>
            To get started, choose an option below:
        </p>
        <ul>
            <li><a href="/wp-admin/post-new.php?post_type=campaign">Create a Campaign</a></li>
            <li><a href="/wp-admin/edit.php?post_type=campaign">View Existing Campaigns</a></li>
        </ul>
    <?
}

function add_vk_dashboard_widget() {
    wp_add_dashboard_widget('vk_dashboard_widget', 'Welcome to VictoryKit', 'vk_dashboard_widget');
}

add_action('wp_dashboard_setup', 'add_vk_dashboard_widget');
