<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>
        <? wp_title('|', true, 'right') ?> VictoryKit
    </title>
    <meta name="viewport" content="width=device-width" />
    <link rel="icon" href="/wp-content/themes/victorykit/images/favicon.png">
    <? wp_head() ?>
    <script>window.$ = window.$ || window.jQuery</script>
</head>
<? global $body_class ?>
<body data-spy="scroll" <? body_class( $body_class ) ?>>

<script type="text/javascript">actionkit.forms.initPage()</script>

<div id="header" class="clearfix container">
    <a href="/"><img src="<?= get_template_directory_uri() ?>/legacy/images/demandprogress-logo_400px.png" width="400" height="55" /></a>
    <div class="follow">
        <a href="https://twitter.com/demandprogress" class="twitter"></a>
        <a href="https://www.facebook.com/demandprogress" class="fb"></a>
    </div>
    <div class="follow-mobile">
        <a href="#big_social">FOLLOW US</a>
    </div>
</div>
<nav class="navbar" id="main_nav">
    <div class="navbar-inner">
    </div>
</nav>

