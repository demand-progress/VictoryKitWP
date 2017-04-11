<?
// CSS class
global $body_class;
$body_class = 'index petition';

// Header
get_header();

// Fields
$disclaimer = $fields['disclaimer'];
$page_headline = $fields['petition_headline'];
$landing_page_body = $fields['landing_page_body'];

// If landing page body is not set, use the email body
if (!$landing_page_body) {
    $landing_page_body = $fields['body'];
}

// If no disclaimer use the default one setup in Advanced Custom Fields for the Disclaimer field
if (!$disclaimer) {
    $disclaimer_field = get_field_object('disclaimer');
    $disclaimer = $disclaimer_field['default_value'];
}
?>

<div id="main" class="container inner-page">
<section class="petition-heading">
    <header>
        <div class="statement-leadin">
            <?= $page_headline ?>
        </div>
    </header>
</section>

<section class="petition-form">
    <!-- Form -->
    <form class="ak-form" name="act" method="POST" action="https://act.demandprogress.org/act/" accept-charset="utf-8">
        <div class="form-wrap">
            <a name="sign"></a>
            <h3>Sign the petition</h3>
            <input type="hidden" name="utf8" value="&#x2714;">
            <ul class="compact" id="ak-errors"></ul>
            <div id="unknown_user">

                <div id="id_name_box" class="">
                    <label for="id_name">Name</label>

                    <input type="text" name="name" id="id_name" class="ak-userfield-input" />

                </div>
                <div id="id_email_box" class="required">
                    <label for="id_email">Email address<span class="ak-required-flag">*</span></label>

                    <input type="text" name="email" id="id_email" class="ak-userfield-input" />

                </div>
                <div id="id_zip_box" class="required">
                    <label for="id_zip">ZIP Code<span class="ak-required-flag">*</span></label>

                    <input type="text" name="zip" id="id_zip" class="ak-userfield-input" />

                </div>
                <div id="id_postal_box" class="">
                    <label for="id_postal">Postal code</label>

                    <input type="text" name="postal" id="id_postal" class="ak-userfield-input">

                </div>
                <input type="hidden" name="country" value="United States">
            </div>
            <div id="known_user">
                Not <span id="known_user_name"></span>?  <a href="?" onclick="return actionkit.forms.logOut()">Click here.</a>
            </div>

            <div class="submit-row">
                <p class="disclaimer"><?= $disclaimer ?></p>
                <a href="https://demandprogress.org/privacy-policy/">Privacy Policy</a>
                <input type="submit" class="blue-button pull-right" value="Sign">
            </div>

        </div>
        <input type="hidden" name="page" value="<?= $ak_page_short_name ?>" />
        <input type="hidden" name="source" value="<?= $source ?>" />
        <input type="hidden" name="lists" value="26" />
    </form>
</section>

<!-- Petition -->
<section class="petition-text">
<article class="padded">
    <div>
        <?= $landing_page_body ?>
    </div>
    <p>Add your name at right to sign the petition:</p>
    <div class="campaign-petition-preview"><?= $fields['petition_text'] ?></div>
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
    get_bloginfo('template_directory') . '/js/single-campaign-default.js'
);

// Footer
get_footer();
?>
