<?php

/**
 * This function’s access is marked private. This means it is not intended for use by plugin or theme developers, only in other core functions.
 *
 * @return void
 */
function __maybe_tgmpa_register(){
	$tgmpa = (array) __get_cache('tgmpa', []);
	if(empty($tgmpa)){
		return;
	}
    foreach($tgmpa as $args){
        __tgmpa($args['plugins'], $args['config']);
    }
}

/**
 * This function MUST be called inside the 'tgmpa_register' action hook.
 *
 * @return void
 */
function __tgmpa($plugins = [], $config = []){
	if(!doing_action('tgmpa_register')){
        return; // Too early or too late.
    }
	$lib = __use_tgm_plugin_activation();
	if(is_wp_error($lib)){
		return; // Silence is golden.
	}
	tgmpa($plugins, $config);
}

/**
 * @return void
 */
function __tgmpa_register($plugins = [], $config = []){
	if(doing_action('tgmpa_register')){ // Just in time.
        __tgmpa($plugins, $config);
		return;
    }
	if(did_action('tgmpa_register')){ // Too late.
		return;
	}
	$tgmpa = [
		'config' => $config,
		'plugins' => $plugins,
	];
	$md5 = __md5($tgmpa);
	__set_array_cache('tgmpa', $md5, $tgmpa);
	__add_action_once('tgmpa_register', '__maybe_tgmpa_register');
}

/**
 * @return bool|WP_Error
 */
function __use_tgm_plugin_activation($ver = '2.6.1'){
	$key = 'tgm-plugin-activation-' . $ver;
    if(__isset_cache($key)){
        return (string) __get_cache($key, '');
    }
	$class = 'TGM_Plugin_Activation';
	if(class_exists($class)){
        return ''; // Already handled outside of this function.
    }
	$dir = __remote_lib('https://github.com/TGMPA/TGM-Plugin-Activation/archive/refs/tags/' . $ver . '.zip', 'TGM-Plugin-Activation-' . $ver);
	if(is_wp_error($dir)){
		return $dir;
	}
	$file = $dir . '/class-tgm-plugin-activation.php';
	if(!file_exists($file)){
		return __error(translate('File doesn&#8217;t exist?'), $file);
	}
	require_once($file);
	if(!class_exists($class)){
		return __error(sprintf(translate('Missing parameter(s): %s'), $class) . '.');
	}
	__set_cache($key, $dir);
	return $dir;
}
