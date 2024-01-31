<?php

/**
 * @return string|WP_Error
 */
function __remote_lib($url = '', $expected_dir = ''){
    $key = md5($url);
    if(__isset_cache($key)){
        return (string) __get_cache($key, '');
    }
	$download_dir = __download_dir();
	if(is_wp_error($download_dir)){
		return $download_dir;
	}
	$fs = __filesystem();
	if(is_wp_error($fs)){
		return $fs;
	}
	$name = 'remote_lib_' . $key;
	$to = $download_dir . '/' . $name;
	if(empty($expected_dir)){
		$expected_dir = $to;
	} else {
		$expected_dir = ltrim($expected_dir, '/');
		$expected_dir = untrailingslashit($expected_dir);
		$expected_dir = $to . '/' . $expected_dir;
	}
	$dirlist = $fs->dirlist($expected_dir, false);
	if(!empty($dirlist)){
        __set_cache($key, $expected_dir);
		return $expected_dir; // Already exists.
	}
	$file = __remote_download($url);
	if(is_wp_error($file)){
		return $file;
	}
	$result = unzip_file($file, $to);
	@unlink($file);
	if(is_wp_error($result)){
		$fs->rmdir($to, true);
		return $result;
	}
	if(!$fs->dirlist($expected_dir, false)){
		$fs->rmdir($to, true);
		return __error(translate('Destination directory for file streaming does not exist or is not writable.'));
	}
    __set_cache($key, $expected_dir);
	return $expected_dir;
}
