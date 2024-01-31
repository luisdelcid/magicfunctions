<?php

/**
 * @return void
 */
function __add_admin_notice($message = '', $class = 'warning', $is_dismissible = false){
	if(doing_action('admin_notices')){ // Just in time.
        __e_admin_notice($message, $class, $is_dismissible);
		return;
    }
	if(did_action('admin_notices')){ // Too late.
		return;
	}
	$admin_notice = [
		'class' => $class,
		'is_dismissible' => $is_dismissible,
		'message' => $message,
	];
	$md5 = __md5($admin_notice);
	__set_array_cache('admin_notices', $md5, $admin_notice);
	__add_action_once('admin_notices', '__maybe_add_admin_notices');
}

/**
 * @return string
 */
function __admin_notice_html($message = '', $class = 'warning', $is_dismissible = false){
	if(!in_array($class, ['error', 'info', 'success', 'warning'])){
		$class = 'warning';
	}
	if($is_dismissible){
		$class .= ' is-dismissible';
	}
	return '<div class="notice notice-' . $class . '"><p>' . $message . '</p></div>';
}

/**
 * This function MUST be called inside the 'admin_notices' action hook.
 *
 * @return void
 */
function __e_admin_notice($message = '', $class = 'warning', $is_dismissible = false){
	if(!doing_action('admin_notices')){
        return; // Too early or too late.
    }
	$html = __admin_notice_html($message, $class, $is_dismissible);
	echo $html;
}

/**
 * This function’s access is marked private. This means it is not intended for use by plugin or theme developers, only in other core functions.
 *
 * @return void
 */
function __maybe_add_admin_notices(){
    $admin_notices = (array) __get_cache('admin_notices', []);
	if(empty($admin_notices)){
		return;
	}
	foreach($admin_notices as $md5 => $admin_notice){
		__e_admin_notice($admin_notice['message'], $admin_notice['class'], $admin_notice['is_dismissible']);
	}
}
