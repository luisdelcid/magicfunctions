<?php

// These functions’ access is marked private.

/**
 * @return string
 */
function __handle(){
    return 'magic-functions';
}

/**
 * This function MUST be called inside the 'after_setup_theme' action hook.
 *
 * @return void
 */
function __require_theme_functions(){
	if(!doing_action('after_setup_theme')){
        return;
    }
    $file = get_stylesheet_directory() . '/magic-functions.php';
    if(!file_exists($file)){
        return;
    }
    require_once($file);
}

/**
 * @return string
 */
function __prefix(){
    return 'magic_functions';
}

/**
 * @return string
 */
function __slug(){
    return 'magic-functions';
}
