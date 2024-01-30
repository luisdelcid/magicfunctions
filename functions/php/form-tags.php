<?php

/**
 * @return bool
 */
function __cf7_is_tag($tag = null){
	return $tag instanceof \WPCF7_FormTag;
}

/**
 * Alias for WPCF7_FormTag::get_class_option.
 *
 * Differs from WPCF7_FormTag::get_class_option in that it will always return a string.
 *
 * @return string
 */
function __cf7_tag_class($tag = null, $default_classes = ''){
    if(!__cf7_is_tag($tag)){
        return '';
    }
	return (string) $tag->get_class_option($default_classes);
}

/**
 * @return string
 */
function __cf7_tag_content($tag = null, $remove_whitespaces = true){
    if(!__cf7_is_tag($tag)){
        return false;
    }
	return ($remove_whitespaces ? __remove_whitespaces($tag->content) : trim($tag->content));
}

/**
 * @return string
 */
function __cf7_tag_content_label($tag = null){
	$content = __cf7_tag_content($tag);
	if(empty($content)){
		return '';
	}
	if(!in_array($tag->basetype, ['checkbox', 'date', 'email', 'file', 'number', 'password', 'radio', 'range', 'select', 'tel', 'text', 'textarea', 'url'])){
        return '';
    }
	if('textarea' === $tag->basetype and $tag->has_option('has_content')){
		return '';
	}
	return $content;
}

/**
 * @return bool
 */
function __cf7_tag_content_placeholder_equals($tag = null){
	$content = __cf7_tag_content($tag);
	$placeholder = __cf7_tag_placeholder($tag);
	return ($content === $placeholder);
}

/**
 * @return string
 */
function __cf7_tag_fa($tag = null){
	$class = __cf7_tag_fa_class($tag);
	if(!$class){
		return '';
	}
	return '<i class="' . $class . '"></i>';
}

/**
 * @return string
 */
function __cf7_tag_fa_class($tag = null){
    $classes = __cf7_tag_fa_classes($tag);
    if(!$classes){
		return '';
	}
	return implode(' ', $classes);
}

/**
 * @return array
 */
function __cf7_tag_fa_classes($tag = null){
	if(!__cf7_is_tag($tag)){
        return [];
    }
	if(!$tag->has_option('fa')){
		return [];
	}
	$classes = [];
	switch(true){
	    case $tag->has_option('fab'):
	        $classes[] = 'fab';
	        break;
	    case $tag->has_option('fad'):
	        $classes[] = 'fad';
	        break;
	    case $tag->has_option('fal'):
	        $classes[] = 'fal';
	        break;
	    case $tag->has_option('far'):
	        $classes[] = 'far';
	        break;
	    case $tag->has_option('fas'):
	        $classes[] = 'fas';
	        break;
	    default:
	        return '';
	}
	$fa = $tag->get_option('fa', 'class', true);
	if(0 !== strpos($fa, 'fa-')){
		$fa = 'fa-' . $fa;
	}
	$classes[] = $fa;
	if($tag->has_option('fw')){
	    $classes[] = 'fa-fw';
	}
    $rotate = $tag->get_option('rotate', 'int', true);
    if(in_array($rotate, [90, 180, 270])){
        $classes[] = 'fa-rotate-' . $rotate;
    }
    $flip = $tag->get_option('flip', '', true);
    if(in_array($flip, ['horizontal', 'vertical', 'both'])){
        $classes[] = 'fa-flip-' . $flip;
    }
    $animate = $tag->get_option('animate', '', true);
    if(in_array($animate, ['beat', 'fade', 'beat-fade', 'bounce', 'flip', 'shake', 'spin'])){
        $classes[] = 'fa-' . $animate;
    }
	return $classes;
}

/**
 * @return string
 */
function __cf7_tag_floating_label($tag = null){
	$content = __cf7_tag_content($tag);
	$placeholder = __cf7_tag_placeholder($tag);
	if(empty($content) and empty($placeholder)){
		return '';
	}
	if(!in_array($tag->basetype, ['date', 'email', 'file', 'number', 'password', 'select', 'tel', 'text', 'textarea', 'url'])){
        return '';
    }
	if($placeholder){
		return $placeholder;
	}
	return wp_strip_all_tags($content);
}

/**
 * @return bool
 */
function __cf7_tag_has_data_option($tag = null){
    if(!__cf7_is_tag($tag)){
        return false;
    }
	return (bool) $tag->get_data_option();
}

/**
 * @return bool
 */
function __cf7_tag_has_content($tag = null){
    $content = __cf7_tag_content($tag);
	return ('' !== $content); // An empty string.
}

/**
 * @return bool
 */
function __cf7_tag_has_free_text($tag = null){
    if(!__cf7_is_tag($tag)){
        return false;
    }
	return $tag->has_option('free_text');
}

/**
 * @return bool
 */
function __cf7_tag_has_pipes($tag = null){
	if(!__cf7_is_tag($tag)){
        return false;
    }
    if(!WPCF7_USE_PIPE or !$tag->pipes instanceof \WPCF7_Pipes or $tag->pipes->zero()){
        return false;
    }
    foreach($tag->pipes->to_array() as $pipe){
        if($pipe[0] !== $pipe[1]){
            return true;
        }
    }
	return false;
}

/**
 * Alias for WPCF7_FormTag::has_option.
 *
 * @return bool
 */
function __cf7_tag_has_option($tag = null, $option_name = ''){
    if(!__cf7_is_tag($tag)){
        return false;
    }
	return $tag->has_option($option_name);
}

/**
 * Important: Avoid WPCF7_FormTag::get_id_option.
 *
 * Differs from WPCF7_FormTag::get_id_option in that it will always return a string.
 *
 * @return string
 */
function __cf7_tag_id($tag = null){
    if(!__cf7_is_tag($tag)){
        return '';
    }
	return __cf7_tag_option($tag, 'id', 'id');
}

/**
 * Opposite of WPCF7_ContactForm::is_true.
 *
 * @return bool
 */
function __cf7_tag_is_false($tag = null, $option_name = ''){
    $option_value = __cf7_tag_option($tag, $option_name);
    return __is_false($option_value);
}

/**
 * Alias for WPCF7_ContactForm::is_true.
 *
 * @return bool
 */
function __cf7_tag_is_true($tag = null, $option_name = ''){
    $option_value = __cf7_tag_option($tag, $option_name);
    return __is_true($option_value);
}

/**
 * Alias for WPCF7_FormTag::get_option(@param $single = true).
 *
 * Differs from WPCF7_FormTag::get_option in that it will always return a string.
 *
 * @return string
 */
function __cf7_tag_option($tag = null, $option_name = '', $pattern = ''){
    if(!__cf7_tag_has_option($tag, $option_name)){
        return '';
    }
    return (string) $tag->get_option($option_name, $pattern, true);
}

/**
 * Alias for WPCF7_FormTag::get_option(@param $single = false).
 *
 * Differs from WPCF7_FormTag::get_option in that it will always return an array.
 *
 * @return array
 */
function __cf7_tag_options($tag = null, $option_name = '', $pattern = ''){
    if(!__cf7_tag_has_option($tag, $option_name)){
        return '';
    }
    return (array) $tag->get_option($option_name, $pattern, false);
}

/**
 * @return string
 */
function __cf7_tag_placeholder($tag = null){
	if(!__cf7_is_tag($tag)){
        return '';
    }
	if(!in_array($tag->basetype, ['date', 'email', 'file', 'number', 'password', 'select', 'tel', 'text', 'textarea', 'url'])){
        return '';
    }
	if('select' === $tag->basetype){
		if($tag->has_option('include_blank') or empty($tag->values)){
			if(version_compare(WPCF7_VERSION, '5.7', '>=')){
				return translate('&#8212;Please choose an option&#8212;', 'contact-form-7'); // Drop-down menu: Uses more friendly label text. https://contactform7.com/2022/12/10/contact-form-7-57/
			} else {
				return '---';
			}
		} elseif($tag->has_option('first_as_label') and !empty($tag->values)){
			return (string) $tag->values[0];
		} else {
			return '';
		}
	} else {
		if(($tag->has_option('placeholder') or $tag->has_option('watermark')) and !empty($tag->values)){
			return (string) $tag->values[0];
		} else {
			return '';
		}
	}
}
