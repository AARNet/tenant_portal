Hello <?php p($_['displayName']); ?>,

There was recently a request to change the password for your CloudStor Collaborator account.

If you requested this password change, please click on the following link to reset your password:

<?php p($_['verifyUrl']); ?>

If clicking the link does not work, please copy and paste the URL into your browser instead.

If you did not make this request, you can ignore this message and your password will remain the same.

Thank you,
The <?php p($theme->getName()); ?> Team
<?php p($theme->getSlogan()); ?>
<?php p($theme->getBaseUrl());?>
