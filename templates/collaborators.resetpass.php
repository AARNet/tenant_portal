<?php
script('tenant_portal', 'collaborators.password');
style('tenant_portal', 'collaborators');
?>

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
        <?php
            if ($_["token"] && $_["user"]) {
        ?>
            <h2>Reset Password for</h2>
            <h2><?php p($_["user"]); ?></h2>
            <p>Please enter a new password for your collaborator account below.</p>
            <form id="collaborator_reset_form">
                <input type="hidden" id="collaborator_token" value="<?php p($_["token"]); ?>">
                <input type="password" id="collaborator_reset_password" class="collaborators_reset_input" placeholder="New password" autocomplete="off">
                <input type="password" id="collaborator_reset_confirm"  class="collaborators_reset_input" placeholder="Confirm password" autocomplete="off">
                <input type="button" id="collaborator_reset_submit" class="button" value="Reset password">
            </form>
        <?php
            } else {
        ?>
        <h2>Invalid token</h2>
        <p>The token provided to reset your password is invalid or has expired.</p>
        <p>You can request a new token by requesting to change you password.</p>
        <p>
            <form action="<?php p(\OC::$server->getUrlGenerator()->linkToRoute('tenant_portal.view.collaborator_request_reset'));?>">
                <input type="submit" class="invalid_token_reset_button" value="Change your password">
            </form>
        </p>
        <?php
            }
        ?>
        </div>
    </div>
</div>
