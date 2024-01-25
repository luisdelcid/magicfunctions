<?php

/**
 * @return string
 */
function __add_action_idx(...$args){
    return __on(...$args);
}

/**
 * @return string
 */
function __add_action_once(...$args){
    return __one(...$args);
}

/**
 * @return string
 */
function __add_filter_idx(...$args){
    return __on(...$args);
}

/**
 * @return string
 */
function __add_filter_once(...$args){
    return __one(...$args);
}

/**
 * @return string
 */
function __callback_idx($callback = null){
    return _wp_filter_build_unique_id('', $callback, 0);
}

/**
 * @return string
 */
function __callback_md5($callback = null){
    $idx = __callback_idx($callback);
    $md5 = md5($idx);
    if(!$callback instanceof \Closure){
        return $md5;
    }
    $md5_closure = __md5_closure($callback);
    if(is_wp_error($md5_closure)){
        return $md5;
    }
    return $md5_closure;
}

/**
 * This function’s access is marked private. This means it is not intended for use by plugin or theme developers, only in other core functions.
 *
 * @return string
 */
function __on($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
    add_filter($hook_name, $callback, $priority, $accepted_args);
    return __callback_idx($callback);
}

/**
 * This function’s access is marked private. This means it is not intended for use by plugin or theme developers, only in other core functions.
 *
 * @return string
 */
function __one($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
    $md5 = __callback_md5($callback);
    $key = $hook_name . '_' . $md5;
    if(__isset_array_cache('hooks', $key)){
        return __get_array_cache('hooks', $key);
    }
    $idx = __on($hook_name, $callback, $priority, $accepted_args);
    __set_array_cache('hooks', $key, $idx);
    return $idx;
}
