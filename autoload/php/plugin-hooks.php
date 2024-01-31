<?php

/**
 * @return string
 */
function __add_plugin_action($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
    $hook_name = __plugin_hook_name($hook_name);
    return __on($hook_name, $callback, $priority, $accepted_args);
}

/**
 * @return string
 */
function __add_plugin_action_once($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
    $hook_name = __plugin_hook_name($hook_name);
    return __one($hook_name, $callback, $priority, $accepted_args);
}

/**
 * @return string
 */
function __add_plugin_filter($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
    $hook_name = __plugin_hook_name($hook_name);
    return __on($hook_name, $callback, $priority, $accepted_args);
}

/**
 * @return string
 */
function __add_plugin_filter_once($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
    $hook_name = __plugin_hook_name($hook_name);
    return __one($hook_name, $callback, $priority, $accepted_args);
}

/**
 * @return mixed
 */
function __apply_plugin_filters($hook_name = '', $value = null, ...$arg){
    $hook_name = __plugin_hook_name($hook_name);
    return apply_filters($hook_name, $value, ...$arg);
}

/**
 * @return bool
 */
function __did_plugin_action($hook_name = ''){
	$hook_name = __plugin_hook_name($hook_name);
	return did_action($hook_name);
}

/**
 * @return bool
 */
function __did_plugin_filter($hook_name = ''){
	$hook_name = __plugin_hook_name($hook_name);
	return did_filter($hook_name);
}

/**
 * @return void
 */
function __do_plugin_action($hook_name = '', ...$arg){
	$hook_name = __plugin_hook_name($hook_name);
	do_action($hook_name, ...$arg);
}

/**
 * @return void
 */
function __do_plugin_action_ref_array($hook_name = '', $args = []){
    $hook_name = __plugin_hook_name($hook_name);
	do_action_ref_array($hook_name, $args);
}

/**
 * @return bool
 */
function __doing_plugin_action($hook_name = ''){
    $hook_name = __plugin_hook_name($hook_name);
    return doing_filter($hook_name);
}

/**
 * @return bool
 */
function __doing_plugin_filter($hook_name = ''){
	$hook_name = __plugin_hook_name($hook_name);
    return doing_filter($hook_name);
}

/**
 * @return bool
 */
function __has_plugin_action($hook_name = '', $callback = false){
	$hook_name = __plugin_hook_name($hook_name);
    return has_filter($hook_name, $callback);
}

/**
 * @return bool
 */
function __has_plugin_filter($hook_name = '', $callback = false){
	$hook_name = __plugin_hook_name($hook_name);
    return has_filter($hook_name, $callback);
}

/**
 * This function’s access is marked private. This means it is not intended for use by plugin or theme developers, only in other core functions.
 *
 * @return string
 */
function __plugin_hook_name($hook_name = ''){
    $file = __backtrace('file', 1);
    $hook_name = __plugin_prefix($hook_name, $file);
    return $hook_name;
}

/**
 * @return bool
 */
function __remove_plugin_action($hook_name = '', $callback = null, $priority = 10){
    $hook_name = __plugin_hook_name($hook_name);
    return remove_filter($hook_name, $callback, $priority);
}

/**
 * @return bool
 */
function __remove_plugin_filter($hook_name = '', $callback = null, $priority = 10){
    $hook_name = __plugin_hook_name($hook_name);
    return remove_filter($hook_name, $callback, $priority);
}
