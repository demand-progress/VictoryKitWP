<?
global $vk_sharing;

require_once('constants.php');
// Debug
if (isset($_GET['topsecretdebug']) && function_exists('top_secret_debug_function')) {
    top_secret_debug_function();
}

// Custom fields
$fields = get_fields();
$ak_page_short_name = get_post_meta($post->ID, 'ak_page_short_name', true);
$VK_LIST_ID = VK_LIST_ID;

$source = 'website';
if (isset($_GET['source'])) {
    $source = htmlspecialchars($_GET['source']);
}

require('single-campaign-share-tags.php');

// Phase
$phase = 'default';
if (isset($_GET['phase'])) {
    $phase = $_GET['phase'];
}

switch ($phase) {
    case 'preview-email':
        require('single-campaign-preview-email.php');
        break;
    case 'thanks':
        require('single-campaign-thanks.php');
        break;
    default:
        require('single-campaign-default.php');
        break;
}
