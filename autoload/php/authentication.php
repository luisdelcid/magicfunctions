<?php

/**
 * This function’s access is marked private. This means it is not intended for use by plugin or theme developers, only in other core functions.
 *
 * This function MUST be called inside the 'authenticate' filter hook.
 *
 * @return bool|WP_Error|WP_User
 */
function __maybe_authenticate_without_password($user, $username_or_email, $password){
	if(!doing_filter('authenticate')){
        return $user;
    }
	if(!is_null($user)){
		return $user;
	}
	if(!empty($password)){
		$message = translate('The link you followed has expired.');
		return __error($message);
	}
	$user = false; // Returning a non-null value will effectively short-circuit the user authentication process.
	if(username_exists($username_or_email)){
		$user = get_user_by('login', $username_or_email);
	} elseif(is_email($username_or_email) and email_exists($username_or_email)){
		$user = get_user_by('email', $username_or_email);
	}
	return $user;
}

/**
 * @return WP_Error|WP_User
 */
function __signon($username_or_email = '', $password = '', $remember = false){
	if(is_user_logged_in()){
		return wp_get_current_user();
	}
	$disable_captcha = !has_filter('wordfence_ls_require_captcha', '__return_false');
	if($disable_captcha){
		add_filter('wordfence_ls_require_captcha', '__return_false');
	}
    $user = wp_signon([
        'remember' => $remember,
        'user_login' => $username_or_email,
        'user_password' => $password,
    ]);
	if($disable_captcha){
		remove_filter('wordfence_ls_require_captcha', '__return_false');
	}
    if(is_wp_error($user)){
        return $user;
    }
    return wp_set_current_user($user->ID);
}

/**
 * @return WP_Error|WP_User
 */
function __signon_without_password($username_or_email = '', $remember = false){
	if(is_user_logged_in()){
		return wp_get_current_user();
	}
    add_filter('authenticate', '__maybe_authenticate_without_password', 10, 3);
	$disable_captcha = !has_filter('wordfence_ls_require_captcha', '__return_false');
	if($disable_captcha){
		add_filter('wordfence_ls_require_captcha', '__return_false');
	}
    $user = wp_signon([
        'remember' => $remember,
        'user_login' => $username_or_email,
        'user_password' => '',
    ]);
	if($disable_captcha){
		remove_filter('wordfence_ls_require_captcha', '__return_false');
	}
    remove_filter('authenticate', '__maybe_authenticate_without_password');
    if(is_wp_error($user)){
        return $user;
    }
    return wp_set_current_user($user->ID);
}
