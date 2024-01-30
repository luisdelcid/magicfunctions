<?php

/**
 * Alias for new WP_Error.
 *
 * @return WP_Error
 */
function __error($message = '', $data = ''){
	if(is_wp_error($message)){
		$data = $message->get_error_data();
		$message = $message->get_error_message();
	}
	if(empty($message)){
		$message = translate('Something went wrong.');
	}
	$code = __str_prefix('error');
	return new \WP_Error($code, $message, $data);
}

/**
 * @return void
 */
function __exit_with_error($message = '', $title = '', $args = []){
	if(is_wp_error($message)){
		$message = $message->get_error_message();
		if($title and !$args){
			$args = $title;
			$title = '';
		}
	}
	if(!$message){
		$message = translate('Error');
	}
	if(!$title){
		$title = translate('Something went wrong.');
	}
	$html = '<h1>' . $title . '</h1>';
	$html .= '<p>';
	$html .= rtrim($message, '.') . '.';
	$referer = wp_get_referer();
	if($referer){
		$back = translate('Go back');
		$html_link = sprintf('<a href="%s">%s</a>', esc_url($referer), $back);
	} else {
		$back = sprintf(translate_with_gettext_context('&larr; Go to %s', 'site'), get_bloginfo('title', 'display'));
		$back = str_replace('&larr;', '', $back);
		$back = trim($back);
		$html_link = sprintf('<a href="%s">%s</a>', esc_url(home_url('/')), $back);
	}
	$html .= ' ' . $html_link . '.';
	$html .= '</p>';
	wp_die($html, $title, $args);
}

/**
 * @return bool|WP_Error
 */
function __is_error($data = []){
	if(is_wp_error($data)){
		return $data;
	}
	if(!__array_keys_exists(['code', 'data', 'message'], $data)){
		return false;
	}
	$count = count($data);
	if((4 === $count and !array_key_exists('additional_errors', $data)) or 3 !== $count){
		return false;
	}
	if(!$data['code'] or !$data['message']){
		return false;
	}
    $error = new \WP_Error($data['code'], $data['message'], $data['data']);
    if(!empty($data['additional_errors'])){
        foreach($data['additional_errors'] as $err){
            if(!__array_keys_exists(['code', 'data', 'message'], $err)){
        		continue;
        	}
            $error->add($err['code'], $err['message'], $err['data']);
        }
    }
	return $error;
}
