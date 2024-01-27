<?php

/**
 * @return string|WP_Error
 */
function __enqueue($handle = '', $src = '', $deps = [], $ver = false, $args_media = null){
	global $wp_version;
	if(!wp_http_validate_url($src)){
		return __error(translate('A valid URL was not provided.'));
	}
	$mimes = [
		'css' => 'text/css',
		'js' => 'application/javascript',
	];
	$filename = __basename($src);
	$filetype = wp_check_filetype($filename, $mimes);
	if(!$filetype['type']){
		return __error(translate('Sorry, you are not allowed to upload this file type.'));
	}
	if(empty($handle)){
		$handle = basename($filename, '.' . $filetype['ext']);
	}
	if('application/javascript' === $filetype['type']){
		$deps[] = __handle();
		$l10n = [];
		if(__is_associative_array($args_media)){
			if(version_compare($wp_version, '6.3', '>=') and (isset($args_media['in_footer']) or isset($args_media['strategy']))){
				$args = $args_media; // As of WordPress 6.3, the new $args parameter – that replaces/overloads the prior $in_footer parameter – can be used to specify a script loading strategy.
			} else {
				$l10n = $args_media;
				$args = true; // In footer.
			}
		} else {
			$args = (bool) $args_media; // $in_footer parameter.
		}
		wp_enqueue_script($handle, $src, $deps, $ver, $args);
		if(!empty($l10n)){
			$object_name = __canonicalize($handle);
			wp_localize_script($handle, $object_name . '_l10n', $l10n);
		}
	} else { // text/css
		if(is_string($args_media)){
			$media = $args_media; // $media parameter.
		} else {
			$media = 'all'; // All.
		}
		wp_enqueue_style($handle, $src, $deps, $ver, $media);
	}
	return $handle;
}

/**
 * @return string|WP_Error
 */
function __local_enqueue($handle = '', $file = '', $deps = [], $args_media = null){
	if(!file_exists($file)){
		return __error(translate('File does not exist! Please double check the name and try again.'));
	}
	$src = __dir_to_url($file);
	$ver = filemtime($file);
	return __enqueue($handle, $src, $deps, $ver, $args_media);
}
