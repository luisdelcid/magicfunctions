<?php

/**
 * @return string
 */
function __base64_urldecode($data = '', $strict = false){
	return base64_decode(strtr($data, '-_', '+/'), $strict);
}

/**
 * @return string
 */
function __base64_urlencode($data = ''){
	return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * @return string
 */
function __canonicalize($key = ''){
	$key = sanitize_title($key);
	$key = str_replace('-', '_', $key);
	return $key;
}

/**
 * @return string
 */
function __first_p($text = '', $dot = true){
	return __one_p($text, $dot, 'first');
}

/**
 * @return string
 */
function __implode_and($array = [], $and = '&'){
	if(!is_array($array)){
		return '';
	}
	if(empty($array)){
		return '';
	}
	if(1 === count($array)){
		return $array[0];
	}
	$last = array_pop($array);
	return implode(', ', $array) . ' ' . trim($and) . ' ' . $last;
}

/**
 * @return string
 */
function __last_p($text = '', $dot = true){
	return __one_p($text, $dot, 'last');
}

/**
 * @return string
 */
function __one_p($text = '', $dot = true, $p = 'first'){
	if(false === strpos($text, '.')){
		if($dot){
			$text .= '.';
		}
		return $text;
	} else {
		$text = sanitize_text_field($text);
		$text = explode('.', $text);
		$text = array_map('trim', $text);
		$text = array_filter($text);
		switch($p){
			case 'first':
				$text = array_shift($text);
				break;
			case 'last':
				$text = array_pop($text);
				break;
			default:
				$p = absint($p);
				if(count($text) >= $p){
					$p --;
					$text = $text[$p];
				} else {
					$text = translate('Error');
				}
		}
		if($dot){
			$text .= '.';
		}
		return $text;
	}
}

/**
 * @return string
 */
function __prepare($str = '', ...$args){
	global $wpdb;
	if(!$args){
		return $str;
	}
	if(false === strpos($str, '%')){
		return $str;
	}
    $subject = $wpdb->prepare($str, ...$args);
    $subject = $wpdb->remove_placeholder_escape($subject);
    return str_replace("'", '', $subject);
}

/**
 * @return string
 */
function __remove_whitespaces($str = ''){
	return trim(preg_replace('/[\r\n\t ]+/', ' ', $str));
}

/**
 * @return string
 */
function __str_prefix($str = '', $prefix = ''){
    $prefix = str_replace('\\', '_', $prefix); // Fix namespaces.
    $prefix = __canonicalize($prefix);
    $prefix = rtrim($prefix, '_');
    if(empty($prefix)){
        $prefix = __prefix();
    }
    $str = __remove_whitespaces($str);
    if(empty($str)){
        return $prefix;
    }
    if(0 === strpos($str, $prefix)){
        return $str; // Text is already prefixed.
    }
    return $prefix . '_' . $str;
}

/**
 * @return string
 */
function __str_slug($str = '', $slug = ''){
    $slug = str_replace('_', '-', $slug); // Fix canonicalized.
    $slug = str_replace('\\', '-', $slug); // Fix namespaces.
	$slug = sanitize_title($slug);
    $slug = rtrim($slug, '-');
    if(!$slug){
        $slug = __slug();
    }
    $str = __remove_whitespaces($str);
    if(empty($str)){
        return $slug;
    }
    if(0 === strpos($str, $slug)){
        return $str; // Text is already slugged.
    }
    return $slug . '-' . $str;
}

/**
 * @return string
 */
function __str_split($str = '', $line_length = 55){
	$str = sanitize_text_field($str);
	$lines = ceil(strlen($str) / $line_length);
	$words = explode(' ', $str);
	if(count($words) <= $lines){
		return $words;
	}
	$length = 0;
	$index = 0;
	$oputput = [];
	foreach($words as $word){
		$word_length = strlen($word);
		if((($length + $word_length) <= $line_length) or empty($oputput[$index])){
			$oputput[$index][] = $word;
			$length += ($word_length + 1);
		} else {
			if($index < ($lines - 1)){
				$index ++;
			}
			$length = $word_length;
			$oputput[$index][] = $word;
		}
	}
	foreach($oputput as $index => $words){
		$oputput[$index] = implode(' ', $words);
	}
	return $oputput;
}

/**
 * @return string
 */
function __str_split_lines($str = '', $lines = 2){
	$str = sanitize_text_field($str);
	$words = explode(' ', $str);
	if(count($words) <= $lines){
		return $words;
	}
	$line_length = ceil(strlen($str) / $lines);
	$length = 0;
	$index = 0;
	$oputput = [];
	foreach($words as $word){
		$word_length = strlen($word);
		if((($length + $word_length) <= $line_length) or empty($oputput[$index])){
			$oputput[$index][] = $word;
			$length += ($word_length + 1);
		} else {
			if($index < ($lines - 1)){
				$index ++;
			}
			$length = $word_length;
			$oputput[$index][] = $word;
		}
	}
	foreach($oputput as $index => $words){
		$oputput[$index] = implode(' ', $words);
	}
	return $oputput;
}
