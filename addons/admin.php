<?php

// Exit if accessed directly
if(!defined('ABSPATH')) exit;

add_filter('preview_post_link', 'vk_admin_preview_post_link', 10, 1);

function vk_admin_preview_post_link($url) {
    if (isset($_COOKIE['vk_admin_preview_email'])) {
        $url .= '&phase=preview-email';
    }

    return $url;
}
