<?php

/**
 * This function’s access is marked private. This means it is not intended for use by plugin or theme developers, only in other core functions.
 *
 * @return string|WP_Error
 */
function __asset_handle($src = ''){
	if(!wp_http_validate_url($src)){
		return __error(translate('A valid URL was not provided.'));
	}
	$filename = __basename($src);
	$mimes = [
		'css' => 'text/css',
		'js' => 'application/javascript',
	];
	$filetype = wp_check_filetype($filename, $mimes);
	if(!$filetype['type']){
		return __error(translate('Sorry, you are not allowed to upload this file type.'));
	}
	$handle = basename($filename, '.' . $filetype['ext']);
	return $handle;
}

/**
 * This function MUST be called inside the 'wp_enqueue_scripts' action hook.
 *
 * @return string|WP_Error
 */
function __enqueue($handle = '', $src = '', $deps = [], $ver = false, $args_media = null){
	global $wp_version;
	if(!doing_action('wp_enqueue_scripts')){ // Too early or too late.
		$message = trim(sprintf(translate('Function %1$s was called <strong>incorrectly</strong>. %2$s %3$s'), 'wp_enqueue_scripts', '', ''));
		return __error($message);
    }
	$asset_handle = __asset_handle($src);
	if(is_wp_error($asset_handle)){
		return $asset_handle;
	}
	if(empty($handle)){
		$handle = $asset_handle;
	}
	$filename = __basename($src);
	$filetype = wp_check_filetype($filename);
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
function __enqueue_asset($handle = '', $src = '', $deps = [], $ver = false, $args_media = null){
	if(doing_action('wp_enqueue_scripts')){ // Just in time.
		return __enqueue($handle, $src, $deps, $ver, $args_media);
	}
	if(did_action('wp_enqueue_scripts')){ // Too late.
		$message = trim(sprintf(translate('Function %1$s was called <strong>incorrectly</strong>. %2$s %3$s'), 'wp_enqueue_scripts', '', ''));
		return __error($message);
	}
	$asset_handle = __asset_handle($src);
	if(is_wp_error($asset_handle)){
		return $asset_handle;
	}
	if(empty($handle)){
		$handle = $asset_handle;
	}
	$asset = [
		'args_media' => $args_media,
		'deps' => $deps,
		'handle' => $handle,
		'src' => $src,
		'ver' => $ver,
	];
	$md5 = __md5($asset);
	__set_array_cache('assets', $md5, $asset);
	__add_action_once('wp_enqueue_scripts', '__maybe_enqueue_assets');
	return $handle;
}

/**
 * @return string|WP_Error
 */
function __enqueue_fa6($ver = '6.5.1'){
	return __enqueue_asset('font-awesome-6', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/' . $ver . '/css/all.min.css', [], $ver);
}

/**
 * @return string|WP_Error
 */
function __enqueue_inputmask($ver = '5.0.8'){
	return __enqueue_asset('jquery-inputmask', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/' . $ver . '/jquery.inputmask.min.js', ['jquery'], $ver);
}

/**
 * @return string|WP_Error
 */
function __enqueue_stylesheet(){
	$handle = get_stylesheet();
	$file = get_stylesheet_directory() . '/style.css';
	return __local_enqueue($handle, $file);
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
	return __enqueue_asset($handle, $src, $deps, $ver, $args_media);
}

/**
 * @return string
 */
function __localize($data = []){
	if(is_string($data)){
		$data = html_entity_decode($data, ENT_QUOTES, 'UTF-8');
	} else {
		foreach((array) $data as $key => $value){
			if(!is_scalar($value)){
				continue;
			}
			$data[$key] = html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
		}
	}
	return wp_json_encode($data);
}

/**
 * This function’s access is marked private. This means it is not intended for use by plugin or theme developers, only in other core functions.
 *
 * @return void
 */
function __maybe_enqueue_assets(){
    $assets = (array) __get_cache('assets', []);
	if(empty($assets)){
		return;
	}
	foreach($assets as $md5 => $asset){
		__enqueue($admin_notice['handle'], $admin_notice['src'], $admin_notice['deps'], $admin_notice['ver'], $admin_notice['args_media']);
	}
}
