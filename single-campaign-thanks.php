<?
// CSS class
global $body_class, $share_meta, $vk_mailings;
$body_class = 'thanks petition';

// Header
get_header();

// Track randomized views
// XXX: we are comparing views of this page to conversions from these sharing values, which doesnt seemn that useful. You dont know how many people actually did the share. We could track how many people click the share buttons at least?
if ($share_meta['randomized']) {
    // TODO: why not track when not randomized?

    global $wpdb;
    $wpdb->insert('vk_share_fb_view', array(
        'campaign_id' => $post->ID,
        'title' => $share_meta['title_index'],
        'description' => $share_meta['description_index'],
        'image' => $share_meta['image_index'],
    ));
}

// TODO: use subject line that was sent to the user, or best one
$email_sharing_subject = "FWD: " . get_post_meta($post->ID, 'subjects_0_subject', true);
$petition_body = get_post_meta($post->ID, 'body', true);
$petition_body = strip_tags(html_entity_decode($petition_body, ENT_QUOTES, 'UTF-8')); // Strip out HTML tags and convert HTML entities into ASCII for plain text email body
$permalink = get_post_permalink($post->ID);
$email_sharing_body = "I just signed this important petition, and I hope you will too: \n\n ----------- \n\n" . $petition_body . "\n\n ----------- \n\n Could you sign too? \n\n $permalink";
$email_sharing_mailto = "mailto:?subject=".rawurlencode($email_sharing_subject)."&body=".rawurlencode($email_sharing_body);
?>

<div id="main" class="container inner-page">
    <section class="thanks-heading">
        <header>
            <div class="statement-leadin">
                You're Almost Done!
            </div>
        </header>
    </section>

    <section class="thanks-text">
        <article class="padded">
            <div class="submitted-message">
              Thank you for taking action. Now please share this important campaign with your friends, and ask them to join in. This will make a big difference in our chances of winning!
            </div>
            <br>
            <div class="share-buttons">
                <a class="facebook" href="#">Share on Facebook</a>
                <a class="email" href="<?= $email_sharing_mailto ?>">Send an Email</a>
            </div>
            <br>
            <br>
            <div class='submitted-message'>
              And please help support our important work on this and other campaigns by donating to the cause!
            </div>
            <br>
            <div class="share-buttons">
                <a id="donate-button" href="<?= $email_sharing_mailto ?>">Donate</a>
            </div>
        </article>
    </section>
</div>

<script type="text/javascript">
    actionkit.forms.contextRoot = 'https://act.demandprogress.org/context/';
    actionkit.forms.initForm('act');
</script>

<?
// Scripts
wp_enqueue_script(
    'single-campaign',
    get_bloginfo('template_directory') . '/js/single-campaign-thanks.js'
);

get_footer();
?>
