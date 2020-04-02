<?php
script('tenant_portal', 'collaborators.password');
style('tenant_portal', 'collaborators');
?>

<script src='https://www.google.com/recaptcha/api.js'></script>
<header>
    <div id="header">
        <a href="<?php print_unescaped(link_to('', 'index.php')); ?>" title="" id="owncloud">
            <div class="logo-icon svg">
            </div>
        </a>
        <div class="header-appname-container">
            <h1 class="header-appname">
                <?php p($theme->getName()); ?>
            </h1>
        </div>

        <div id="logo-claim" style="display:none;"><?php p($theme->getLogoClaim()); ?></div>
    </div>
</header>
<div id="content-wrapper">
    <div id="content">
        <div class="collaborators_reset_password section">
            <h2>Reset your password</h2>
            <p>Enter your username below and a verification email will be sent to you.</p>
            <form id="collaborator_request_reset_form">
                <input type="text" id="collaborator_uid" class="collaborators_reset_input" placeholder="Username/E-mail address">
                <div class="g-recaptcha" data-sitekey="6Lcv2SMUAAAAALeUBDOVWRNiVc7jhPeUARERUDUb"></div>
                <input type="button" id="collaborator_submit" class="button" value="Request Reset">
            </form>
        </div>
    </div>
</div>
