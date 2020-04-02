Hello <?php p($_['displayName']); ?>,

An OwnCloud Collaborator account has been created for you by <?php p($_['tenantName']); ?>.

Your username is: <?php p($_['collaborator']); ?>

In order to login, you will need to visit the below link and reset your account password:
<?php p($_['resetUrl']); ?>

If clicking the link does not work, please copy and paste the URL into your browser instead.

Thank you,
The <?php p($theme->getName()); ?> Team
<?php p($theme->getSlogan()); ?>
<?php p($theme->getBaseUrl());?>
