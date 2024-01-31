<?php

/**
 * @return mixed
 */
function __get_array_cache($array_key = '', $key = '', $default = null){
	$array = (array) __get_cache($array_key, []);
	return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * @return mixed
 */
function __get_cache($key = '', $default = null){
	$group = ___prefix();
	$value = wp_cache_get($key, $group, false, $found);
	if($found){
		return $value;
	}
    return $default;
}

/**
 * @return bool
 */
function __isset_array_cache($array_key = '', $key = ''){
	$array = (array) __get_cache($array_key, []);
	return isset($array[$key]);
}

/**
 * @return bool
 */
function __isset_cache($key = ''){
	$group = ___prefix();
	$value = wp_cache_get($key, $group, false, $found);
    return $found;
}

/**
 * @return bool
 */
function __set_array_cache($array_key = '', $key = '', $data = null){
	$array = (array) __get_cache($array_key, []);
	$array[$key] = $data;
	return __set_cache($array_key, $array);
}

/**
 * @return bool
 */
function __set_cache($key = '', $data = null){
	$group = ___prefix();
	return wp_cache_set($key, $data, $group);
}

/**
 * @return bool
 */
function __unset_array_cache($array_key = '', $key = ''){
	$array = (array) __get_cache($array_key, []);
	if(isset($array[$key])){
		unset($array[$key]);
	}
	return __set_cache($array_key, $array);
}

/**
 * @return bool
 */
function __unset_cache($key = ''){
	$group = ___prefix();
	return wp_cache_delete($key, $group);
}
