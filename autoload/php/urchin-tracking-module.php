<?php

/**
 * @return array
 */
function __current_utm(){
    $keys = __utm_keys();
    $utm = [];
	foreach($keys as $key){
        if(isset($_GET[$key])){
			$utm[$key] = $_GET[$key];
            continue;
		}
        $name = __utm_cookie($key);
        if(isset($_COOKIE[$name])){
			$utm[$key] = $_COOKIE[$name];
            continue;
		}
        $utm[$key] = '';
    }
    return $utm;
}

/**
 * @return void
 */
function __enqueue_utm(){
    if(doing_action('wp_enqueue_scripts')){ // Just in time.
        __localize_utm();
		return;
    }
	if(did_action('wp_enqueue_scripts')){ // Too late.
		return;
	}
    __set_cache('utm', true);
	__add_action_once('after_setup_theme', '__maybe_set_utm');
    __add_action_once('wp_enqueue_scripts', '__maybe_enqueue_utm');
}

/**
 * This function MUST be called inside the 'wp_enqueue_scripts' action hook.
 *
 * @return void
 */
function __localize_utm(){
    if(!doing_action('wp_enqueue_scripts')){ // Too early or too late.
		return;
    }
    $current = __current_utm();
    $handle = __handle();
    $object_name = __str_prefix('utm');
    $query_string = build_query($current);
    $current['utm_hash'] = md5($query_string);
    $current['utm_query'] = $query_string;
	ksort($current);
    wp_localize_script($handle, $object_name, $current);
}

/**
 * This function’s access is marked private. This means it is not intended for use by plugin or theme developers, only in other core functions.
 *
 * @return void
 */
function __maybe_enqueue_utm(){
    $continue = (bool) __get_cache('utm', false);
    if(!$continue){
        return;
    }
    __localize_utm();
}

/**
 * @return void
 */
function __maybe_set_utm(){
    $continue = (bool) __get_cache('utm', false);
    if(!$continue){
        return;
    }
	$at_least_one = false;
	$keys = __utm_keys();
	foreach($keys as $key){
		if(isset($_GET[$key])){
			$at_least_one = true;
			break;
		}
	}
	if(!$at_least_one){
		return;
	}
	__maybe_unset_utm();
	$cookie_lifetime = time() + WEEK_IN_SECONDS;
	$secure = ('https' === parse_url(home_url(), PHP_URL_SCHEME));
	foreach($keys as $key){
		if(!isset($_GET[$key])){
			continue;
		}
        $value = wp_unslash($_GET[$key]);
		$value = esc_attr($value);
        $name = __utm_cookie($key);
		setcookie($name, $value, $cookie_lifetime, COOKIEPATH, COOKIE_DOMAIN, $secure);
	}
}

/**
 * @return void
 */
function __maybe_unset_utm(){
    $keys = __utm_keys();
    $past = time() - YEAR_IN_SECONDS;
	foreach($keys as $key){
        $name = __utm_cookie($key);
		if(!isset($_COOKIE[$name])){
			continue;
		}
		setcookie($name, ' ', $past, COOKIEPATH, COOKIE_DOMAIN);
	}
}

/**
 * @return string
 */
function __utm_cookie($name = ''){
    $keys = __utm_keys();
    if(!in_array($name, $keys)){
        return '';
    }
    $name = __str_prefix($name . '_' . COOKIEHASH);
    return $name;
}

/**
 * @return array
 */
function __utm_keys(){
	$utm = __utm_pairs();
	return array_keys($utm);
}

/**
 * @return string
 */
function __utm_name($name = ''){
    $pairs = __utm_pairs();
    if(!array_key_exists($name, $pairs)){
        return '';
    }
    return $pairs[$name];
}

/**
 * @return array
 */
function __utm_pairs(){
	$utm = [
		'utm_campaign' => 'Name',
		'utm_content' => 'Content',
		'utm_id' => 'ID',
		'utm_medium' => 'Medium',
		'utm_source' => 'Source',
		'utm_term' => 'Term',
	];
	return $utm;
}
