<?php

/**
 * @return string
 */
function __basename($path = '', $suffix = ''){
	return wp_basename(preg_replace('/\?.*/', '', $path), $suffix);
}

/**
 * @return string|WP_Error
 */
function __check_upload_dir($path = ''){
	$path = wp_normalize_path($path);
	$upload_dir = wp_get_upload_dir();
	if($upload_dir['error']){
		return __error($upload_dir['error']);
	}
	$basedir = wp_normalize_path($upload_dir['basedir']);
	if(!str_starts_with($path, $basedir)){
		$error_msg = sprintf(translate('Unable to locate needed folder (%s).'), translate('The uploads directory'));
		return __error($error_msg);
	}
	return $path;
}

/**
 * Alias for WP_REST_Attachments_Controller::check_upload_size.
 *
 * @return bool|WP_Error
 */
function __check_upload_size($file_size = 0){
	if(!is_multisite()){
		return true;
	}
	if(get_site_option('upload_space_check_disabled')){
		return true;
	}
	$space_left = get_upload_space_available();
	if($space_left < $file_size){
		$error_msg = sprintf(translate('Not enough space to upload. %s KB needed.'), number_format(($file_size - $space_left) / KB_IN_BYTES));
		return __error($error_msg);
	}
	if($file_size > (KB_IN_BYTES * get_site_option('fileupload_maxk', 1500))){
		$error_msg = sprintf(translate('This file is too big. Files must be less than %s KB in size.'), get_site_option('fileupload_maxk', 1500));
		return __error($error_msg);
	}
	if(!function_exists('upload_is_user_over_quota')){
		require_once(ABSPATH . 'wp-admin/includes/ms.php'); // Include multisite admin functions to get access to upload_is_user_over_quota().
	}
	if(upload_is_user_over_quota(false)){
		$error_msg = translate('You have used your space quota. Please delete files before uploading.');
		return __error($error_msg);
	}
	return true;
}

/**
 * @return string
 */
function __dir_to_url($path = ''){
	return str_replace(wp_normalize_path(ABSPATH), site_url('/'), wp_normalize_path($path));
}

/**
 * @return string|WP_Error
 */
function __download_dir(){
	$upload_dir = wp_get_upload_dir();
	if($upload_dir['error']){
		return __error($upload_dir['error']);
	}
	$path = $upload_dir['basedir'];
    $dir = __str_slug('downloads');
	$download_dir = $path . '/' . $dir;
	return __mkdir_p($download_dir);
}

/**
 * @return WP_Filesystem_Base|WP_Error
 */
function __filesystem(){
	global $wp_filesystem;
	if(!function_exists('get_filesystem_method')){
		require_once(ABSPATH . 'wp-admin/includes/file.php');
	}
	if('direct' !== get_filesystem_method()){
		return __error(translate('Could not access filesystem.')); // TODO: determine the best way to support other filesystem methods.
	}
	if($wp_filesystem instanceof \WP_Filesystem_Base){
		return $wp_filesystem;
	}
	if(!WP_Filesystem()){
		return __error(translate('Filesystem error.'));
	}
	return $wp_filesystem;
}

/**
 * @return int
 */
function __get_memory_size(){
	if(!function_exists('exec')){
		$current_limit = ini_get('memory_limit');
		$current_limit_int = wp_convert_hr_to_bytes($current_limit);
		return $current_limit_int;
	}
	exec('free -b', $output);
	$output = sanitize_text_field($output[1]);
	$output = explode(' ', $output);
	return (int) $output[1];
}

/**
 * @return array|WP_Error
 */
function __handle_file($file = [], $dir = '', $mimes = null){
	if(empty($file)){
		$error_msg = translate('No data supplied.');
		return __error($error_msg);
	}
	if(!is_array($file)){
		if(!is_scalar($file)){
			$error_msg = translate('Invalid data provided.');
			return __error($error_msg);
		}
		if(empty($_FILES[$file])){
			$error_msg = translate('File does not exist! Please double check the name and try again.');
			return __error($error_msg);
		}
		$file = $_FILES[$file];
	}
	$keys = ['error', 'name', 'size', 'tmp_name', 'type'];
	foreach($keys as $key){
		$file[$key] = isset($file[$key]) ? (array) $file[$key] : [];
	}
	$count = count($file['tmp_name']);
	$files = [];
	for($i = 0; $i < $count; $i ++){
		$files[$i] = [];
		foreach($keys as $key){
			if(isset($file[$key][$i])){
				$files[$i][$key] = $file[$key][$i];
			}
		}
	}
	$uploaded_files = [];
	foreach($files as $index => $file){
		$uploaded_files[$index] = __handle_upload($file, $dir, $mimes);
	}
	return $uploaded_files;
}

/**
 * @return array|WP_Error
 */
function __handle_files($files = [], $dir = '', $mimes = null){
	if(empty($files)){
		if(empty($_FILES)){
			$error_msg = translate('No data supplied.');
			return __error($error_msg);
		}
		$files = $_FILES;
	}
	$uploaded_files = [];
	foreach($files as $key => $file){
		$uploaded_files[$key] = __handle_file($file, $dir, $mimes);
	}
	return $uploaded_files;
}

/**
 * @return string|WP_Error
 */
function __handle_upload($file = [], $dir = '', $mimes = null){
	if(!empty($dir) and (!@is_dir($dir) or !wp_is_writable($dir))){
		$error_msg =  translate('Destination directory for file streaming does not exist or is not writable.');
		return __error($error_msg);
	}
	if(empty($file)){
		$error_msg = translate('No data supplied.');
		return __error($error_msg);
	}
	$file = shortcode_atts([
		'error' => 0,
		'name' => '',
		'size' => 0,
		'tmp_name' => '',
		'type' => '',
	], $file);
	$uploaded_file = __test_uploaded_file($file['tmp_name']);
	if(is_wp_error($uploaded_file)){
		return $uploaded_file;
	}
	$error = __test_error($file['error']);
	if(is_wp_error($error)){
		return $error;
	}
	$size = __test_size($file['size']);
	if(is_wp_error($size)){
		return $size;
	}
	$filename = __test_type($file['tmp_name'], $file['name'], $mimes);
	if(is_wp_error($filename)){
		return $filename;
	}
	$size_check = __check_upload_size($file['size']);
	if(is_wp_error($size_check)){
		return $size_check;
	}
	if($dir){
		$upload_dir = __check_upload_dir($dir);
		if(is_wp_error($upload_dir)){
			return $upload_dir;
		}
	} else {
		$upload_dir = wp_upload_dir();
		if($upload_dir['error']){
			return __error($upload_dir['error']);
		}
		$dir = $upload_dir['path'];
	}
	$filename = wp_unique_filename($dir, $filename);
	$new_file = path_join($dir, $filename);
	$move_new_file = @move_uploaded_file($file['tmp_name'], $new_file);
	if(false === $move_new_file){
		$error_path = str_replace(ABSPATH, '', $dir);
		$error_msg = sprintf(translate('The uploaded file could not be moved to %s.'), $error_path);
		return __error($error_msg);
	}
	$stat = stat(dirname($new_file));
	$perms = $stat['mode'] & 0000666;
	chmod($new_file, $perms); // Set correct file permissions.
	if(is_multisite()){
		clean_dirsize_cache($new_file);
	}
	return $new_file;
}

/**
 * @return bool
 */
function __is_extension_allowed($extension = ''){
	foreach(wp_get_mime_types() as $exts => $mime){
		if(preg_match('!^(' . $exts . ')$!i', $extension)){
			return true;
		}
	}
	return false;
}

/**
 * Alias for wp_mkdir_p.
 *
 * Differs from wp_mkdir_p in that it will return an error if path wasn't created.
 *
 * @return string|WP_Error
 */
function __mkdir_p($target = ''){
	$key = md5($target);
	if(__isset_array_cache('mkdir_p', $key)){
		return (string) __get_array_cache($key, '');
	}
	if(!wp_mkdir_p($target)){
		return __error(translate('Could not create directory.'));
	}
	if(!wp_is_writable($target)){
		return __error(translate('Destination directory for file streaming does not exist or is not writable.'));
	}
	__set_array_cache('mkdir_p', $key, $target);
	return $target;
}

/**
 * @return string
 */
function __read_file_chunk($handle = null, $chunk_size = 0, $chunk_lenght = 0){
	$giant_chunk = '';
	if(is_resource($handle) and $chunk_size){
		$byte_count = 0;
		if(!$chunk_lenght){
			$chunk_lenght = 8 * KB_IN_BYTES;
		}
		while(!feof($handle)){
			$chunk = fread($handle, $chunk_lenght);
			$byte_count += strlen($chunk);
			$giant_chunk .= $chunk;
			if($byte_count >= $chunk_size){
				return $giant_chunk;
			}
		}
	}
	return $giant_chunk;
}

/**
 * @return bool|WP_Error
 */
function __test_error($error = 0){ // A successful upload will pass this test.
	$upload_error_strings = [
		false,
		sprintf(translate('The uploaded file exceeds the %1$s directive in %2$s.'), 'upload_max_filesize', 'php.ini'),
		sprintf(translate('The uploaded file exceeds the %s directive that was specified in the HTML form.'), 'MAX_FILE_SIZE'),
		translate('The uploaded file was only partially uploaded.'),
		translate('No file was uploaded.'),
		'',
		translate('Missing a temporary folder.'),
		translate('Failed to write file to disk.'),
		translate('File upload stopped by extension.'),
	]; // Courtesy of php.net, the strings that describe the error indicated in $_FILES[{form field}]['error'].
	if($error > 0){
		if(empty($upload_error_strings[$error])){
			$error_msg = translate('Something went wrong.');
		} else {
			$error_msg = $upload_error_strings[$error];
		}
		return __error($error_msg);
	}
	return true;
}

/**
 * @return bool|WP_Error
 */
function __test_size($file_size = 0){ // A non-empty file will pass this test.
	if(0 === $file_size){
		if(is_multisite()){
			$error_msg = translate('File is empty. Please upload something more substantial.');
		} else {
			$error_msg = sprintf(translate('File is empty. Please upload something more substantial. This error could also be caused by uploads being disabled in your %1$s file or by %2$s being defined as smaller than %3$s in %1$s.'), 'php.ini', 'post_max_size', 'upload_max_filesize');
		}
		return __error($error_msg);
	}
	return true;
}

/**
 * @return string|WP_Error
 */
function __test_type($tmp_name = '', $name = '', $mimes = null){ // A correct MIME type will pass this test.
	$wp_filetype = wp_check_filetype_and_ext($tmp_name, $name, $mimes);
	$ext = empty($wp_filetype['ext']) ? '' : $wp_filetype['ext'];
	$type = empty($wp_filetype['type']) ? '' : $wp_filetype['type'];
	$proper_filename = empty($wp_filetype['proper_filename']) ? '' : $wp_filetype['proper_filename']; // Check to see if wp_check_filetype_and_ext() determined the filename was incorrect.
	if($proper_filename){
		$name = $proper_filename;
	}
	if((!$type or !$ext) and !current_user_can('unfiltered_upload')){
		$error_msg = translate('Sorry, you are not allowed to upload this file type.');
		return __error($error_msg);
	}
	return $name;
}

/**
 * @return bool|WP_Error
 */
function __test_uploaded_file($tmp_name = ''){ // A properly uploaded file will pass this test.
	if(!is_uploaded_file($tmp_name)){
		$error_msg = translate('Specified file failed upload test.');
		return __error($error_msg);
	}
	return true;
}
