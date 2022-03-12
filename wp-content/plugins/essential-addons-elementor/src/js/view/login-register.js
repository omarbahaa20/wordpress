/*--- Pro Version --- */
ea.hooks.addAction("init", "ea", () => {
    const EALoginRegisterPro = function ($scope, $) {
        const $wrap = $scope.find('.eael-login-registration-wrapper');// cache wrapper
        const ajaxEnabled = $wrap.data('is-ajax');
        const widgetId = $wrap.data('widget-id');
        const redirectTo = $wrap.data('redirect-to');
        const $loginForm = $wrap.find('#eael-login-form');
        window.isLoggedInByFB = false;
        window.isUsingGoogleLogin = false;
        // Google
        const gLoginNodeId = 'eael-google-login-btn-' + widgetId;
        const $gBtn = $loginForm.find('#' + gLoginNodeId);
        // Facebook
        const fLoginNodeId = 'eael-fb-login-btn-' + widgetId;
        const $fBtn = $loginForm.find('#' + fLoginNodeId);
        const $registerForm = $wrap.find('#eael-register-form');
        const ajaxAction = {
            name: "action",
            value: 'eael-login-register-form'
        };
        const valid_login_vendors = ['facebook', 'google', 'login'];
        const $passField = $registerForm.find('#form-field-password');
        const psOps = $registerForm.find('.pass-meta-info').data('strength-options');
        const $passNotice = $registerForm.find('.eael-pass-notice');
        const $passMeter = $registerForm.find('.eael-pass-meter');
        const $passHint = $registerForm.find('.eael-pass-hint');

        const showPassMeta = ($passField.length > 0 && ($passNotice.length > 0 || $passMeter.length > 0 || $passHint.length > 0));

        const sendData = function sendData(form_data, formType) {
            // set the correct form type we are submitting: login or register?
            form_data.push({
                "name": `eael-${formType}-submit`,
                "value": true
            });
            form_data.push(ajaxAction);

            $.ajax({
                url: localize.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: form_data,
                success: function (data) {
                    const success = (data && data.success);
                    const isLoginForm = valid_login_vendors.includes(formType);
                    let message;
                    if (success) {
                        message = `<div class="eael-form-msg valid">${data.data.message}</div>`;
                    } else {
                        message = `<div class="eael-form-msg invalid">${data.data}</div>`;
                    }

                    if (isLoginForm) {
                        if(!success){
                            $loginForm.find("#eael-login-submit").prop("disabled",false);
                        }
                        $loginForm.find('.eael-form-validation-container').html(message);
                    } else {
                        $registerForm.find("#eael-register-submit").prop("disabled",false);
                        $registerForm.find('.eael-form-validation-container').html(message);
                    }

                    //handle redirect
                    if (success) {
                        if (data.data.redirect_to) {
                            setTimeout(() => window.location = data.data.redirect_to, 500);
                        } else if (isLoginForm) {
                            // refresh the page on login success
                            setTimeout(() => location.reload(), 1000);
                        }
                    }


                },
                error: function (xhr, err) {
                    let errorHtml = `
                    <p class="eael-form-msg invalid">
                    Error occurred: ${err.toString()} 
                    </p>
                    `;
                    if ('login' === formType) {
                        $loginForm.find("#eael-login-submit").prop("disabled",false);
                        $loginForm.find('.eael-form-validation-container').html(errorHtml);
                    } else {
                        $registerForm.find("#eael-register-submit").prop("disabled",false);
                        $registerForm.find('.eael-form-validation-container').html(errorHtml);
                    }
                }
            });
        }
        if ('yes' === ajaxEnabled) {

            //Handle Register form submission via ajax
            $loginForm.on('submit', function (e) {
                $loginForm.find("#eael-login-submit").prop("disabled",true);
                const form_data = $(this).serializeArray()
                form_data.filter((currentValue, index) => {
                    if (form_data[index].name == 'eael-login-nonce') {
                        form_data[index].value = localize.eael_login_nonce;
                        return;
                    }
                });
                sendData(form_data, 'login');
                return false;
            });

            //Handle Register form submission via ajax
            $registerForm.on('submit', function (e) {
                $registerForm.find("#eael-register-submit").prop("disabled",true);
                const form_data = $(this).serializeArray()
                form_data.filter((currentValue, index) => {
                    if (form_data[index].name == 'eael-register-nonce') {
                        form_data[index].value = localize.eael_register_nonce;
                    }
                });
                sendData(form_data, 'register');
                return false;
            });
        }


        if ($gBtn.length && !isEditMode) {
            let gClientId = $gBtn.data('g-client-id');

            // Login with Google
            if (typeof gapi !== 'undefined' && gapi !== null) {
                gapi.load('auth2', function () {
                    auth2 = gapi.auth2.init({
                        client_id: gClientId,
                        cookiepolicy: 'single_host_origin',
                    });

                    auth2.attachClickHandler(document.getElementById(gLoginNodeId), {},

                        function (googleUser) {

                            let profile = googleUser.getBasicProfile();
                            let name = profile.getName();
                            let email = profile.getEmail();
                            if (window.isUsingGoogleLogin) {
                                let id_token = googleUser.getAuthResponse().id_token;
                                let googleData = [
                                    {
                                        name: 'widget_id',
                                        value: widgetId,
                                    },
                                    {
                                        name: 'redirect_to',
                                        value: redirectTo,
                                    },
                                    {
                                        name: 'email',
                                        value: email,
                                    },
                                    {
                                        name: 'full_name',
                                        value: name,
                                    },
                                    {
                                        name: 'id_token',
                                        value: id_token,
                                    }, {
                                        name: 'nonce',
                                        value: $loginForm.find('#eael-login-nonce').val(),
                                    },
                                ];

                                sendData(googleData, 'google');
                            }

                        }, function (error) {
                            let msg = `<p class="eael-form-msg invalid"> Something went wrong! ${error.error}</p>`
                            $scope.find('.eael-form-validation-container').html(msg);
                        }
                    );

                });
            } else {
                console.log('gapi not defined or loaded');
            }

        }

        if ($fBtn.length && !isEditMode) {
            let appId = $fBtn.data('fb-appid');
            window.fbAsyncInit = function () {
                FB.init({
                    appId: appId,
                    cookie: true,
                    xfbml: true,
                    version: 'v8.0'
                });

                FB.AppEvents.logPageView();

            };

            (function (d, s, id) {
                var js,
                    fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) {return;}
                js = d.createElement(s);
                js.id = id;
                js.src = "https://connect.facebook.net/en_US/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));


            $fBtn.on('click', function () {

                if (!isLoggedInByFB) {
                    FB.login(function (response) {
                        // handle the response
                        if (response.status === 'connected') {
                            // Logged into our webpage and Facebook.
                            logUserInOurAppUsingFB();
                        } else {
                            console.log('The person is not logged into our webpage or facebook is unable to tell.')
                        }
                    }, {scope: 'public_profile,email'});
                }

            });

            // Fetch the user profile data from facebook.
            function logUserInOurAppUsingFB() {
                FB.api('/me', {fields: 'id, name, email'},
                    function (response) {
                        window.isLoggedInByFB = true;
                        let fbData = [
                            {
                                name: 'widget_id',
                                value: widgetId,
                            },
                            {
                                name: 'redirect_to',
                                value: redirectTo,
                            },
                            {
                                name: 'email',
                                value: response.email,
                            },
                            {
                                name: 'full_name',
                                value: response.name,
                            },
                            {
                                name: 'user_id',
                                value: response.id,
                            },
                            {
                                name: 'access_token',
                                value: FB.getAuthResponse()['accessToken'],
                            },
                            {
                                name: 'nonce',
                                value: $loginForm.find('#eael-login-nonce').val(),
                            },
                        ];

                        sendData(fbData, 'facebook');

                    });

            }
        }

        $gBtn.on('click', function (e) {
            window.isUsingGoogleLogin = true;
        });


        // Password Strength Related meta information
        if (showPassMeta) {
            function showStrengthMeter(strength, password) {
                if ('yes' !== psOps.show_ps_meter) {
                    return;
                }
                if (!password) {
                    $passMeter.hide(300);
                    return;
                }
                $passMeter.show(400);
                const meterValue = 0 === strength ? 1 : strength;
                $passMeter.val(meterValue);
            }

            function showStrengthText(strength, password) {
                if ('yes' !== psOps.show_pass_strength) {
                    return;
                }
                if (!password) {
                    $passNotice.hide(300);
                    return;
                }
                $passNotice.show(400);
                let pText = '';
                const useCustomText = ('custom' === psOps.ps_text_type);
                const cssClasses = 'short bad mismatch good strong';

                switch (strength) {
                    case -1:
                        // do nothing
                        break;
                    case 2:
                        pText = useCustomText ? psOps.ps_text_bad : pwsL10n.bad;
                        $passNotice.html(pText).removeClass(cssClasses).addClass('bad');

                        break;
                    case 3:
                        pText = useCustomText ? psOps.ps_text_good : pwsL10n.good;
                        $passNotice.html(pText).removeClass(cssClasses).addClass('good');
                        break;
                    case 4:
                        pText = useCustomText ? psOps.ps_text_strong : pwsL10n.strong;
                        $passNotice.html(pText).removeClass(cssClasses).addClass('strong');

                        break;
                    case 5:
                        $passNotice.html(pwsL10n.mismatch).removeClass(cssClasses).addClass('mismatch');
                        break;
                    default:
                        pText = useCustomText ? psOps.ps_text_short : pwsL10n.short;
                        $passNotice.html(pText).removeClass(cssClasses).addClass('short');
                }
            }

            function togglePassHint(strength) {
                if (strength >= 3) {
                    $passHint.hide(300); // hide hint when pass word is good.
                } else {
                    $passHint.show(400);
                }
            }

            function checkPassStrength() {
                let strength;
                let password = $passField.val();
                if (password) {
                    strength = wp.passwordStrength.meter(password, wp.passwordStrength.userInputDisallowedList(), password);// @todo; add confirm pass check later
                }
                showStrengthMeter(strength, password)
                showStrengthText(strength, password);
                togglePassHint(strength);
            }

            $passField.on('keyup', function (e) {
                checkPassStrength();
            });
        }

    };
    elementorFrontend.hooks.addAction("frontend/element_ready/eael-login-register.default", EALoginRegisterPro);
});

