<?
// CSS class
global $vk_mailings;

// Fields
$fields['url'] = get_permalink();
$fields['wrap'] = true;
get_post_meta($post->ID, 'ak_page_short_name', true);
//$fields['subject'] = get_post_meta($post->ID, 'subjects_0_subject', true);
$fields['salutation'] = "<p>Hi Francine,</p>"; // TODO: Salutation doesnt seem to actually be used anywhere
$fields['body'] = get_field('body', $post->ID);
$fields['petition_headline'] = get_post_meta($post->ID, 'petition_headline', true);
$html = $vk_mailings->render($fields);
echo $html;

?>

<script>
    // Clear cookie
    document.cookie = 'vk_admin_preview_email;max-age=0;path=/';
</script>
