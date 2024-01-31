<?php

/**
 * @return string
 */
function __alert($message = '', $class = '', $is_dismissible = false){
	if(!in_array($class, ['danger', 'dark', 'info', 'light', 'primary', 'secondary', 'success', 'warning'])){
		$class = 'warning';
	}
	if($is_dismissible){
		$class .= ' alert-dismissible fade show';
	}
	if($is_dismissible){
		$message .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
	}
	return '<div class="alert alert-' . $class . '">' . $message . '</div>';
}

/**
 * @return string
 */
function __alert_danger($message = '', $is_dismissible = false){
	return __alert($message, 'danger', $is_dismissible);
}

/**
 * @return string
 */
function __alert_dark($message = '', $is_dismissible = false){
	return __alert($message, 'dark', $is_dismissible);
}

/**
 * @return string
 */
function __alert_info($message = '', $is_dismissible = false){
	return __alert($message, 'info', $is_dismissible);
}

/**
 * @return string
 */
function __alert_light($message = '', $is_dismissible = false){
	return __alert($message, 'light', $is_dismissible);
}

/**
 * @return string
 */
function __alert_primary($message = '', $is_dismissible = false){
	return __alert($message, 'primary', $is_dismissible);
}

/**
 * @return string
 */
function __alert_secondary($message = '', $is_dismissible = false){
	return __alert($message, 'secondary', $is_dismissible);
}

/**
 * @return string
 */
function __alert_success($message = '', $is_dismissible = false){
	return __alert($message, 'success', $is_dismissible);
}

/**
 * @return string
 */
function __alert_warning($message = '', $is_dismissible = false){
	return __alert($message, 'warning', $is_dismissible);
}
