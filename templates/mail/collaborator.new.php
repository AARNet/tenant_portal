<!doctype html>
<html>
    <head>
        <meta name="viewport" content="width=device-width">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>CloudStor</title>
        <style>
            * { font-family: sans-serif; font-size: 100%; line-height: 1.6em; margin: 0; padding: 0; }
            body { -webkit-font-smoothing: antialiased; height: 100%; -webkit-text-size-adjust: none; width: 100% !important; }
            a { font-family: sans-serif; color: #348eda; }
            table.body-wrap { 
                padding: 20px; 
                width: 100%;
            }
            table.footer-wrap {
                clear: both !important;
                width: 100%;
            }

            .footer-wrap .container p {
                color: #666666;
                font-size: 12px;

            }

            table.footer-wrap a {
                color: #999999;
            }

            h3 {
                color: #111111;
                font-family: sans-serif;
                font-weight: 200;
                line-height: 1.2em;
                margin: 20px 0 10px;
                font-size: 22px;
            }

            td {
                font-family: sans-serif;
                font-size: 14px;
            }

            p {
                font-family: sans-serif;
                font-size: 14px;
                font-weight: normal;
                margin-bottom: 10px;
            }

            .container {
                clear: both !important;
                display: block !important;
                margin: 0 auto !important;
                border: 1px solid #f0f0f0;
            }

            .body-wrap .container {
                padding: 20px;
            }

            .content {
                display: block;
                margin: 0 auto;
            }

            .content table {
                width: 100%;
            }

            .highlight {
                background-color: #f1f1f1;
                border: 1px solid #e1e1e1;
                padding: 10px 5px;
            }
        </style>
    </head>
    <body bgcolor="#f6f6f6">
    <!-- body -->
        <table class="body-wrap" bgcolor="#f6f6f6">
            <tr>
                <td class="container" bgcolor="#FFFFFF">

                    <!-- content -->
                    <div class="content">
                    <table>
                        <tr>
                            <td>
<h3>Hello <?php p($_['displayName']); ?>,<h3>
<p>An OwnCloud Collaborator account has been created for you by <strong><?php p($_['tenantName']); ?></strong>.</p>
<p>Your username is <strong><?php p($_['collaborator']); ?></strong></p>
<p>In order to login, you will need to visit the below link and reset your account password:
<p><a href="<?php p($_['resetUrl']); ?>" class="highlight"><?php p($_['resetUrl']); ?></a></p>
<p>If clicking the link does not work, please copy and paste the URL into your browser instead.</p>
                                </div>
                                <p>&nbsp;</p>
                                <p>Thank you,<br> <strong>The <?php p($theme->getName()); ?> Team</strong><br />
                                <?php p($theme->getSlogan()); ?>
                                <br><a href="<?php p($theme->getBaseUrl()); ?>"><?php p($theme->getBaseUrl());?></a></p>
                            </td>
                        </tr>
                    </table>
                    </div>
                    <!-- /content -->
                </td>
                <td></td>
            </tr>
        </table>
    <!-- /body -->
    </body>
</html>
