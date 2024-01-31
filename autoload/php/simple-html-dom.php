<?php

/**
 * @return simple_html_dom|WP_Error
 */
function __file_get_html(...$args){
	$remote_lib = __use_simple_html_dom();
	if(is_wp_error($remote_lib)){
		return $remote_lib;
	}
	return file_get_html(...$args);
}

/**
 * @return simple_html_dom|WP_Error
 */
function __str_get_html(...$args){
	$remote_lib = __use_simple_html_dom();
	if(is_wp_error($remote_lib)){
		return $remote_lib;
	}
	return str_get_html(...$args);
}

/**
 * @return bool|WP_Error
 */
function __use_simple_html_dom($ver = '1.9.1'){
	$key = 'simplehtmldom-' . $ver;
    if(__isset_cache($key)){
        return (string) __get_cache($key, '');
    }
	$class = 'simple_html_dom';
	if(class_exists($class)){
        return ''; // Already handled outside of this function.
    }
	$dir = __remote_lib('https://github.com/simplehtmldom/simplehtmldom/archive/refs/tags/' . $ver . '.zip', 'simplehtmldom-' . $ver);
	if(is_wp_error($dir)){
		return $dir;
	}
	$file = $dir . '/simple_html_dom.php';
	if(!file_exists($file)){
		return __error(translate('File doesn&#8217;t exist?'), $file);
	}
	require_once($file);
	if(!class_exists($class)){
		return __error(sprintf(translate('Missing parameter(s): %s'), $class) . '.');
	}
	__set_cache($key, $dir);
	return $dir;
}
