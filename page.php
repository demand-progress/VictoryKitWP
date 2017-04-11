<?
// CSS class
global $body_class;
$body_class = 'index petition';

// Custom fields
$fields = get_fields();

// Header
get_header()
?>

<div id="main" class="container inner-page">
    <section class="petition-heading">
        <header>
            <div class="statement-leadin">Welcome to VictoryKit</div>
        </header>
    </section>
    
    <section class="petition-text">
        <p>
            VictoryKit helps you quickly A/B test petitions and find the ones that resonate. Integrated with your CRM, sprinkled with magic, and crafted with love.
        </p>
        <p>
            You can enter the <a href="/wp-admin/edit.php?post_type=campaign">campaigner interface here</a>.
        </p>
    </section>
</div>


<? get_footer() ?>
