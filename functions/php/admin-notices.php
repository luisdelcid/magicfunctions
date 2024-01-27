<?php

/**
 * This function MUST be called inside the 'admin_notices' action hook.
 *
 * @return void
 */
function __admin_notice($message = '', $class = 'warning', $is_dismissible = false){
	if(!doing_action('admin_notices')){
        return;
    }
	$html = __admin_notice_html($message, $class, $is_dismissible);
	$md5 = md5($html);
	if(__isset_array_cache('admin_notices', $md5)){
		return;
	}
	__set_array_cache('admin_notices', $md5, $html);
	echo $html;
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
