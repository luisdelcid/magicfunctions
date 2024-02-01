<?php

/**
 * @return void
 */
function __login_form_login($post_id = 0){
    $post = get_post($post_id);
    if(is_null($post) or 'page' !== $post->post_type or 'publish' !== $post->post_status){
        return;
    }
    __set_array_cache('login_forms', 'login', $post->ID);
    __action_once('login_form_login', '__maybe_redirect_login_form_login');
}

/**
 * @return void
 */
function __login_form_lostpassword($post_id = 0){
    $post = get_post($post_id);
    if(is_null($post) or 'page' !== $post->post_type or 'publish' !== $post->post_status){
        return;
    }
    __set_array_cache('login_forms', 'lostpassword', $post->ID);
    __action_once('login_form_lostpassword', '__maybe_redirect_login_form_lostpassword');
    __action_once('login_form_retrievepassword', '__maybe_redirect_login_form_lostpassword');
}

/**
 * @return void
 */
function __login_form_register($post_id = 0){
    $post = get_post($post_id);
    if(is_null($post) or 'page' !== $post->post_type or 'publish' !== $post->post_status){
        return;
    }
    __set_array_cache('login_forms', 'register', $post->ID);
    __action_once('login_form_register', '__maybe_redirect_login_form_register');
}

/**
 * @return void
 */
function __login_form_reset_resetpass($post_id = 0){
    $post = get_post($post_id);
    if(is_null($post) or 'page' !== $post->post_type or 'publish' !== $post->post_status){
        return;
    }
    __set_array_cache('login_forms', 'resetpass', $post->ID);
    __action_once('login_form_resetpass', '__maybe_redirect_login_form_resetpass');
    __action_once('login_form_rp', '__maybe_redirect_login_form_resetpass');
}

/**
 * @return void
 */
function __login_form_retrievepassword($post_id = 0){
    __login_form_lostpassword($post_id);
}

/**
 * @return void
 */
function __login_form_rp($post_id = 0){
    __login_form_reset_resetpass($post_id);
}

/**
 * @return void
 */
function __maybe_redirect_login_form_login(){
    if(!__isset_array_cache('login_forms', 'login')){
        return;
    }
    if(isset($_REQUEST['interim-login'])){
        return;
    }
    $post_id = (int) __get_array_cache('login_forms', 'login', 0);
    $url = get_permalink($post_id);
    if($_GET){
        $url = add_query_arg($_GET, $url);
    }
    wp_safe_redirect($url);
    exit;
}

/**
 * @return void
 */
function __maybe_redirect_login_form_lostpassword(){
    if(!__isset_array_cache('login_forms', 'lostpassword')){
        return;
    }
    $post_id = (int) __get_array_cache('login_forms', 'lostpassword', 0);
    $url = get_permalink($post_id);
    if($_GET){
        $url = add_query_arg($_GET, $url);
    }
    wp_safe_redirect($url);
    exit;
}

/**
 * @return void
 */
function __maybe_redirect_login_form_register(){
    if(!__isset_array_cache('login_forms', 'register')){
        return;
    }
    $post_id = (int) __get_array_cache('login_forms', 'register', 0);
    $url = get_permalink($post_id);
    if($_GET){
        $url = add_query_arg($_GET, $url);
    }
    wp_safe_redirect($url);
    exit;
}

/**
 * @return void
 */
function __maybe_redirect_login_form_resetpass(){
    if(!__isset_array_cache('login_forms', 'resetpass')){
        return;
    }
    $post_id = (int) __get_array_cache('login_forms', 'resetpass', 0);
    $url = get_permalink($post_id);
    if($_GET){
        $url = add_query_arg($_GET, $url);
    }
    wp_safe_redirect($url);
    exit;
}
