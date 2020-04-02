( function() {
	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}
	if (!OCA.TenantPortal.Collaborators) {
		OCA.TenantPortal.Collaborators = {};
	}

	OCA.TenantPortal.Collaborators.Password = {
		initialise: function() {
			cmd = OCA.TenantPortal.Collaborators.Password;
			// Request reset elements/events
			cmd.getTokenForm = $('#collaborator_request_reset_form');
			cmd.getTokenButton = $('#collaborator_submit');
			cmd.getTokenField = $('#collaborator_uid');
			cmd.getTokenButton.on('click', function() { cmd.generateToken(); });
			cmd.getTokenForm.on('submit', function(e) { 
				cmd.generateToken();
				e.preventDefault();
			});

			// Reset password elements/events
			cmd.tokenForm = $('#collaborator_reset_form');
			cmd.tokenField = $('#collaborator_token');
			cmd.newPasswordField = $('#collaborator_reset_password');
			cmd.confirmPasswordField = $('#collaborator_reset_confirm');
			cmd.resetPasswordButton = $('#collaborator_reset_submit');
			cmd.resetPasswordButton.on('click', function() { cmd.resetPassword(); });
			cmd.tokenForm.on('submit', function(e) {
				cmd.resetPassword();
				e.preventDefault();
			});
		},
		baseUrl: function(path) {
			path = $.trim(path);
			if (path && path[0] != '/') {
				path = '/'+path;
			}
			return OC.generateUrl('/apps/tenant_portal/collaborators'+path)
		},
		generateToken: function() {
			cmd = OCA.TenantPortal.Collaborators.Password;
			cmd.displayMessage(false);
			captchaToken = $('#g-recaptcha-response').val();
			if (captchaToken === "") {
				cmd.displayMessage(cmd.getTokenForm, 'Please confirm that you are not a robot by completing the CAPTCHA.', 'fail');
			} else {
				$.post(
					cmd.baseUrl('generateResetToken'),
					{ 'collaborator_uid': cmd.getTokenField.val(), 'captcha_token': captchaToken },
					function (result) {
						if (result.code) {
							cmd.displayMessage(cmd.getTokenForm, 'Successfully requested a password reset. If this is a valid account, an email will have been sent to the associated email address.', 'success');
							cmd.getTokenForm.hide();
						} 
					}
				).fail(function(result) {
						cmd.displayMessage(cmd.getTokenForm, 'Unable to process your request at this time. Please try again later.', 'fail');
				});
			}
		},
		resetPassword: function() {
			cmd = OCA.TenantPortal.Collaborators.Password;
			token = cmd.tokenField.val();
			pwd = cmd.newPasswordField.val();
			confirmpwd = cmd.confirmPasswordField.val();
			cmd.displayMessage(false);
			if (pwd.length === 0) {
				cmd.displayMessage(cmd.tokenForm, "You must specify a new password", 'fail');
				return false;
			}
			if (confirmpwd.length === 0) {
				cmd.displayMessage(cmd.tokenForm, "You must confirm your new password", 'fail');
				return false;
			}
			if (confirmpwd !== pwd) {
				cmd.displayMessage(cmd.tokenForm, "The specified passwords do not match", 'fail');
				return false;
			}
			$.post(
					cmd.baseUrl('resetPassword'),
					{ 'token': token, 'password': pwd, 'confirm': confirmpwd },
					function (result) {
						if (result.code === 200) {
							full_url = window.location.protocol + '//' + window.location.host + OC.webroot;
							cmd.displayMessage(cmd.tokenForm, 'Successfully reset password, <a href="'+full_url+'">click here to login</a>', 'success');
							cmd.tokenForm.hide();
						} else {
							cmd.displayMessage(cmd.tokenForm, result.message, 'fail');
						}
					}
				).fail(function(result) {
					console.log('failed');
					cmd.displayMessage(cmd.tokenForm, 'Unable to process you request at this time. Please try again later.', 'fail');
				}
			);
		},
		// Creates a div for displaying a success or failure message
		displayMessage: function(before, message, style) {
			if (before === false) {
				if ($("#result_message")) {
					$("#result_message").remove();
				}
				return false;
			}
			if ((typeof before !== 'undefined') && (typeof message !== 'undefined')) {
				if (typeof style === 'undefined') {
					style = 'success';
				}
				if ($("#result_message")) {
					$("#result_message").remove();
				}
				$("<div>").attr("id","result_message")
					.addClass(style)
					.html(message)
					.insertBefore(before);
			}
		},
	};

})();

$(document).ready(function(){OCA.TenantPortal.Collaborators.Password.initialise();});
