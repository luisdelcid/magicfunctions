<?php

/**
 * @return string|WP_Error
 */
function __class_enqueue($file = '', $deps = [], $args_media = null){
	if(file_exists($file)){
        $caller_file = $file;
		$file_exists = true;
	} else {
        $caller_file = __backtrace('file');
		$file_exists = false;
	}
    $caller_class = __backtrace('class');
    if(!class_exists($caller_class)){
		return __error(translate('Invalid object type.'));
	}
	$filename = wp_basename($file);
	$mimes = [
		'css' => 'text/css',
		'js' => 'application/javascript',
	];
	$filetype = wp_check_filetype($filename, $mimes);
	if(!$filetype['type']){
		return __error(translate('Sorry, you are not allowed to upload this file type.'));
	}
	if(!$file_exists){
        $file = plugin_dir_path($caller_file) . $filename; // Relative to the caller file.
        if(!file_exists($file)){
            return __error(translate('File does not exist! Please double check the name and try again.'));
        }
	}
	$basename = basename($filename, '.' . $filetype['ext']);
	$handle = __class_slug($basename, $caller_class);
	return __local_enqueue($handle, $file, $deps, $args_media);
}

/**
 * @return string
 */
function __class_prefix($str = '', $class = ''){
	if(empty($class)){
        $class = __backtrace('class');
	}
	if(!class_exists($class)){
		return '';
	}
	return __str_prefix($str, $class);
}

/**
 * @return string
 */
function __class_slug($str = '', $class = ''){
    if(empty($class)){
        $class = __backtrace('class');
	}
    if(!class_exists($class)){
		return '';
	}
	return __str_slug($str, $class);
}
