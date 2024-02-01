<?php

/**
 * @return bool
 */
function __bb_is(){
    if(__isset_cache('bb_is')){
        return (bool) __get_cache('bb_is', false);
    }
	$current_theme = wp_get_theme();
	$bb_is = ('Beaver Builder Theme' === $current_theme->get('Name') or 'bb-theme' === $current_theme->get('Template'));
	__set_cache('bb_is', $bb_is);
	return $bb_is;
}

/**
 * @return bool
 */
function __bb_is_b4(){
    if(__isset_cache('bb_is_b4')){
        return (bool) __get_cache('bb_is_b4', false);
    }
    if(!__bb_is()){
        return false;
    }
    $framework = get_theme_mod('fl-framework', 'base');
	$bb_is_b4 = ('bootstrap-4' === $framework);
    __set_cache('bb_is_b4', $bb_is_b4);
	return $bb_is_b4;
}

/**
 * @return bool
 */
function __bb_is_fa5(){
    if(__isset_cache('bb_is_fa5')){
        return (bool) __get_cache('bb_is_fa5', false);
    }
    if(!__bb_is()){
        return false;
    }
    $awesome = get_theme_mod('fl-awesome', 'none');
	$bb_is_fa5 = ('fa5' === $awesome);
    __set_cache('bb_is_fa5', $bb_is_fa5);
	return $bb_is_fa5;
}
