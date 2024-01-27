<?php

/**
 * @return bool
 */
function __are_plugins_active($plugins = []){
	if(!is_array($plugins)){
		return false;
	}
	foreach($plugins as $plugin){
		if(!__is_plugin_active($plugin)){
			return false;
		}
	}
	return true;
}

/**
 * @return bool
 */
function __is_plugin_active($plugin = ''){
	if(__isset_array_cache('active_plugins', $plugin)){
		return (bool) __get_array_cache('active_plugins', $plugin, false);
	}
	if(!function_exists('is_plugin_active')){
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
	}
	$status = is_plugin_active($plugin);
	__set_array_cache('active_plugins', $plugin, $status);
    return $status;
}

/**
 * @return bool
 */
function __is_plugin_deactivating($file = ''){
	global $pagenow;
	if(!@is_file($file)){
		return false;
	}
	return (is_admin() and 'plugins.php' === $pagenow and isset($_GET['action'], $_GET['plugin']) and 'deactivate' === $_GET['action'] and plugin_basename($file) === $_GET['plugin']);
}

/**
 * @return array
 */
function __mu_plugins(){
	if(__isset_cache('mu_plugins')){
		return (array) __get_cache('mu_plugins', []);
	}
	$mu_plugins = wp_get_mu_plugins();
	__set_cache('mu_plugins', $mu_plugins);
	return $mu_plugins;
}

/**
 * @return string
 */
function __plugin_basename($file = ''){
	if(empty($file)){
        $file = __backtrace('file');
	}
	$plugin_file = __plugin_file($file);
	if(!$plugin_file){
		return '';
	}
	return plugin_basename($plugin_file);
}

/**
 * @return array|WP_Error
 */
function __plugin_data($file = '', $markup = true, $translate = true){
	if(empty($file)){
        $file = __backtrace('file');
	}
    $plugin_file = __plugin_file($file);
	if(empty($plugin_file)){
		return __error(translate('Plugin not found.'));
	}
    $md5 = __md5([$plugin_file, $markup, $translate]);
	if(__isset_array_cache('plugin_data', $md5)){
		return (array) __get_array_cache('plugin_data', $md5, []);
	}
    if(!function_exists('get_plugin_data')){
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    $data = get_plugin_data($plugin_file, $markup, $translate);
	__set_array_cache('plugin_data', $md5, $data);
    return $data;
}

/**
 * @return string|WP_Error
 */
function __plugin_enqueue($file = '', $deps = [], $args_media = null){
	if(file_exists($file)){
		$caller_file = $file;
		$file_exists = true;
	} else {
		$caller_file = __backtrace('file');
		$file_exists = false;
	}
	$filename = wp_basename($file);
	$plugin_file = __plugin_file($caller_file);
	if(!$plugin_file){
		return __error(translate('Plugin not found.'));
	}
	$mimes = [
		'css' => 'text/css',
		'js' => 'application/javascript',
	];
	$filetype = wp_check_filetype($filename, $mimes);
	if(!$filetype['type']){
		return __error(translate('Sorry, you are not allowed to upload this file type.'));
	}
	if(!$file_exists){
		$file = plugin_dir_path($plugin_file) . 'src/' . $filetype['ext'] . '/' . $filename; // Relative to the directory path for the plugin's main file.
		if(!file_exists($file)){
			$file = plugin_dir_path($caller_file) . $filename; // Relative to the caller file.
			if(!file_exists($file)){
				return __error(translate('File does not exist! Please double check the name and try again.'));
			}
		}
	}
	$basename = basename($filename, '.' . $filetype['ext']);
	$handle = __plugin_slug($basename, $plugin_file);
	return __local_enqueue($handle, $file, $deps, $args_media);
}

/**
 * @return string
 */
function __plugin_file($file = ''){
	global $wp_plugin_paths;
	if(empty($file)){
        $file = __backtrace('file');
	} else {
        if(!file_exists($file)){
    		return ''; // File does not exist.
    	}
    }
    $md5 = md5($file);
    if(__isset_array_cache('plugin_files', $md5)){
		return (string) __get_array_cache('plugin_files', $md5, '');
	}
	$file = wp_normalize_path($file); // $wp_plugin_paths contains normalized paths.
	arsort($wp_plugin_paths);
	foreach($wp_plugin_paths as $dir => $realdir){
        if(str_starts_with($file, $realdir)){
			$file = $dir . substr($file, strlen($realdir));
		}
	}
	$plugin_dir = wp_normalize_path(WP_PLUGIN_DIR);
	$mu_plugin_dir = wp_normalize_path(WPMU_PLUGIN_DIR);
    if(preg_match('#^' . preg_quote($plugin_dir, '#') . '/#', $file)){ // File is a plugin.
        $dir = $plugin_dir;
        $file = preg_replace('#^' . preg_quote($plugin_dir, '#') . '/#', '', $file); // Get relative path from plugins directory.
        $mu_plugin = false;
    } elseif(preg_match('#^' . preg_quote($mu_plugin_dir, '#') . '/#', $file)){ // File is a must-use plugin.
        $dir = $mu_plugin_dir;
        $file = preg_replace('#^' . preg_quote($mu_plugin_dir, '#') . '/#', '', $file); // Get relative path from must-use plugins directory.
        $mu_plugin = true;
    } else { // File is not a plugin.
        __set_array_cache('plugin_files', $md5, '');
		return '';
    }
	$file = trim($file, '/');
    $parts = explode('/', $file);
    if(count($parts) <= 2){ // The entire plugin consists of just a single PHP file, like Hello Dolly or file is the plugin's main file.
        if($mu_plugin or __is_plugin_active($file)){ // Plugin is a must-use plugin or plugin is active.
            $file = $dir . '/' . $file;
            __set_array_cache('plugin_files', $md5, $file);
            return $file;
        }
		__set_array_cache('plugin_files', $md5, '');
		return ''; // Plugin is inactive.
    }
	$dir_path = trailingslashit($parts[0]);
    if($mu_plugin){ // Rarely needed.
		$mu_plugins = __mu_plugins();
		foreach($mu_plugins as $mu_plugin){
	        if(str_starts_with($mu_plugin, $dir_path)){
	            $file = $dir . '/' . $mu_plugin;
	            __set_array_cache('plugin_files', $md5, $file);
	            return $file; // Plugin is a must-use plugin.
	        }
		}
        __set_array_cache('plugin_files', $md5, '');
        return ''; // An unexpected error occurred.
    }
	$active_plugins = (array) get_option('active_plugins', []);
	foreach($active_plugins as $active_plugin){
        if(str_starts_with($active_plugin, $dir_path)){
            $file = $dir . '/' . $active_plugin;
            __set_array_cache('plugin_files', $md5, $file);
            return $file; // Plugin is active.
        }
	}
	$active_sitewide_plugins = (array) get_site_option('active_sitewide_plugins', []);
	$active_sitewide_plugins = array_keys($active_sitewide_plugins);
	foreach($active_sitewide_plugins as $active_sitewide_plugin){
        if(str_starts_with($active_sitewide_plugin, $dir_path)){
            $file = $dir . '/' . $active_sitewide_plugin;
            __set_cache($key, $file);
            return $file; // Plugin is active for the entire network.
        }
	}
    __set_array_cache('plugin_files', $md5, '');
    return ''; // Plugin is inactive.
}

/**
 * @return string
 */
function __plugin_folder($file = ''){
	if(empty($file)){
        $file = __backtrace('file');
	}
	$basename = __plugin_basename($file); // The plugin's main file.
	$parts = explode('/', $basename);
	if(count($parts) < 2){ // The entire plugin consists of just a single PHP file, like Hello Dolly.
		return ''; // Ignore.
	}
	$parts = explode('/', $basename, 2);
	return $parts[0];
}

/**
 * @return string
 */
function __plugin_meta($key = '', $file = ''){
	if(empty($file)){
        $file = __backtrace('file');
	}
    $data = __plugin_data($file);
    if(is_wp_error($data)){
        return $data->get_error_message();
    }
	if(array_key_exists($key, $data)){
		$arr = $data;
	} elseif(array_key_exists($key, $data['sections'])){
		$arr = $data['sections'];
	} else {
		return $key . ' ' . translate('(not found)');
	}
	return $arr[$key];
}

/**
 * @return string
 */
function __plugin_prefix($str = '', $file = ''){
	if(empty($file)){
        $file = __backtrace('file');
	}
	$plugin_folder = __plugin_folder($file);
	if(!$plugin_folder){
		return '';
	}
	return __str_prefix($str, $plugin_folder);
}

/**
 * @return string
 */
function __plugin_slug($str = '', $file = ''){
	if(empty($file)){
        $file = __backtrace('file');
	}
	$plugin_folder = __plugin_folder($file);
	if(!$plugin_folder){
		return '';
	}
	return __str_slug($str, $plugin_folder);
}
