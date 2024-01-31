<?php

/**
 * This function MUST be called inside the 'wp_head' action hook.
 *
 * @return void
 */
function __e_hide_recaptcha_badge(){
	if(!doing_action('wp_head')){
        return;
    } ?>
    <style type="text/css">
        .grecaptcha-badge {
            visibility: hidden !important;
        }
    </style><?php
}

/**
 * @return void
 */
function __hide_recaptcha_badge(){
	if(doing_action('wp_head')){ // Just in time.
        __e_hide_recaptcha_badge();
		return;
    }
	if(did_action('wp_head')){ // Too late.
		return;
	}
	__add_action_once('wp_head', '__maybe_hide_recaptcha_badge');
}

/**
 * @return bool|string
 */
function __is_google_workspace($email = ''){
	if(!is_email($email)){
		return false;
	}
	list($local, $domain) = explode('@', $email, 2);
	if('gmail.com' === strtolower($domain)){
		return 'gmail.com';
	}
	if(!getmxrr($domain, $mxhosts)){
		return false;
	}
	if(!in_array('aspmx.l.google.com', $mxhosts)){
		return false;
	}
	return $domain;
}

/**
 * This function’s access is marked private. This means it is not intended for use by plugin or theme developers, only in other core functions.
 *
 * @return void
 */
function __maybe_hide_recaptcha_badge(){
	__e_hide_recaptcha_badge();
}

/**
 * @return string
 */
function __recaptcha_branding(){
	return 'This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="_blank">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank">Terms of Service</a> apply.';
}
