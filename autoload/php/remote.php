<?php

/**
 * @return bool
 */
function __is_cloudflare(){
	return isset($_SERVER['CF-ray']); // TODO: Check for Cloudflare Enterprise.
}

/**
 * @return array|bool
 */
function __is_wp_http_request($args = [], $method_verification = false){
	if(!is_array($args)){
		return false;
	}
	$wp_http_request_args = ['method', 'timeout', 'redirection', 'httpversion', 'user-agent', 'reject_unsafe_urls', 'blocking', 'headers', 'cookies', 'body', 'compress', 'decompress', 'sslverify', 'sslcertificates', 'stream', 'filename', 'limit_response_size'];
	$wp_http_request = true;
	foreach(array_keys($args) as $arg){
		if(!in_array($arg, $wp_http_request_args)){
			$wp_http_request = false;
			break;
		}
	}
	if(!$method_verification){
		return $wp_http_request;
	}
	if(empty($args['method'])){
		return false;
	}
	if(!in_array($args['method'], ['DELETE', 'GET', 'HEAD', 'OPTIONS', 'PATCH', 'POST', 'PUT', 'TRACE'])){
		return false;
	}
	return $args;
}

/**
 * @return string
 */
function __remote_country(){
	switch(true){
		case !empty($_SERVER['HTTP_CF_IPCOUNTRY']):
			$country = $_SERVER['HTTP_CF_IPCOUNTRY']; // Cloudflare.
			break;
		case is_callable(['wfUtils', 'IP2Country']):
			$country = \wfUtils::IP2Country(__remote_ip()); // Wordfence.
			break;
		default:
			$country = '';
	}
	return strtoupper($country); // ISO 3166-1 alpha-2.
}

/**
 * @return array|string|WP_Error
 */
function __remote_delete($url = '', $args = []){
	return __remote_request('DELETE', $url, $args);
}

/**
 * @return string|WP_Error
 */
function __remote_download($url = '', $args = []){
	$args = wp_parse_args($args, [
		'filename' => '',
		'timeout' => 300,
	]);
	if(empty($args['filename'])){
		$download_dir = __download_dir();
		if(is_wp_error($download_dir)){
			return $download_dir;
		}
		$filename = __basename($url);
		$filename = wp_unique_filename($download_dir, $filename);
		$args['filename'] = trailingslashit($download_dir) . $filename;
	} else {
		$filename = __check_upload_dir($args['filename']);
		if(is_wp_error($filename)){
			return $filename;
		}
	}
	$args['stream'] = true;
	$response = __remote_get($url, $args);
	if(is_wp_error($response)){
		@unlink($args['filename']);
		return $response;
	}
	return $args['filename'];
}

/**
 * @return array|string|WP_Error
 */
function __remote_get($url = '', $args = []){
	return __remote_request('GET', $url, $args);
}

/**
 * @return array|string|WP_Error
 */
function __remote_head($url = '', $args = []){
	return __remote_request('HEAD', $url, $args);
}

/**
 * @return string
 */
function __remote_ip($default = ''){
	switch(true){
		case !empty($_SERVER['HTTP_CF_CONNECTING_IP']):
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP']; // Cloudflare.
			break;
		case (__is_plugin_active('wordfence/wordfence.php') and is_callable(['wfUtils', 'getIP'])):
			$ip = \wfUtils::getIP(); // Wordfence.
			break;
		case !empty($_SERVER['HTTP_X_FORWARDED_FOR']):
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			break;
		case !empty($_SERVER['HTTP_X_REAL_IP']):
			$ip = $_SERVER['HTTP_X_REAL_IP'];
			break;
		case !empty($_SERVER['REMOTE_ADDR']):
			$ip = $_SERVER['REMOTE_ADDR'];
			break;
		default:
			return $default;
	}
	if(false === strpos($ip, ',')){
		$ip = trim($ip);
	} else {
		$ip = explode(',', $ip);
		$ip = array_map('trim', $ip);
		$ip = array_filter($ip);
		if(empty($ip)){
			return $default;
		}
		$ip = $ip[0];
	}
	if(!\WP_Http::is_ip_address($ip)){
		return $default;
	}
	return $ip;
}

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

/**
 * @return array|string|WP_Error
 */
function __remote_options($url = '', $args = []){
	return __remote_request('OPTIONS', $url, $args);
}

/**
 * @return array|string|WP_Error
 */
function __remote_patch($url = '', $args = []){
	return __remote_request('PATCH', $url, $args);
}

/**
 * @return array|string|WP_Error
 */
function __remote_post($url = '', $args = []){
	return __remote_request('POST', $url, $args);
}

/**
 * @return array|string|WP_Error
 */
function __remote_put($url = '', $args = []){
	return __remote_request('PUT', $url, $args);
}

/**
 * @return array|string|WP_Error
 */
function __remote_request($method = '', $url = '', $args = []){
	$args = __sanitize_remote_args($args);
	if(empty($args['cookies'])){
		$location = wp_sanitize_redirect($url);
		if(wp_validate_redirect($location)){
			$args['cookies'] = $_COOKIE;
		}
	}
	$args['method'] = strtoupper($method);
	if(empty($args['user-agent'])){
		if(empty($_SERVER['HTTP_USER_AGENT'])){
			$args['user-agent'] = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36'; // Example Chrome UA string: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/User-Agent#chrome_ua_string
		} else {
			$args['user-agent'] = $_SERVER['HTTP_USER_AGENT'];
		}
	}
	$response = wp_remote_request($url, $args);
	if(is_wp_error($response)){
		return $response;
	}
	$body = wp_remote_retrieve_body($response);
	$code = wp_remote_retrieve_response_code($response);
	$headers = wp_remote_retrieve_headers($response);
	$request = new \WP_REST_Request($method);
	$request->set_body($body);
	$request->set_headers($headers);
	$is_valid = $request->has_valid_params();
	if(is_wp_error($is_valid)){
		return $is_valid; // Regardless of the response code.
	}
	$is_json = $request->is_json_content_type();
	$json_params = [];
	if($is_json){
		$json_params = $request->get_json_params();
		$error = __is_error($json_params);
		if(is_wp_error($error)){
			return $error; // Regardless of the response code.
		}
	}
	if($code >= 200 and $code < 300){
		if($is_json){
			return $json_params;
		}
		return $body;
	}
	$message = wp_remote_retrieve_response_message($response);
	if(empty($message)){
		$message = get_status_header_desc($code);
	}
	if(empty($message)){
		$message = translate('Something went wrong.');
	}
	return __error($message, [
		'body' => $body,
		'headers' => $headers,
		'status' => $code,
	]);
}

/**
 * @return bool|WP_Error
 */
function __remote_request_is_json($args = []){
	if(!__is_wp_http_request($args, true)){
		return __error(translate('Invalid request method.'));
	}
	if(empty($args['headers'])){
		return false;
	}
	$request = new \WP_REST_Request($args['method']);
	$request->set_headers($args['headers']);
	return $request->is_json_content_type();
}

/**
 * @return array|string|WP_Error
 */
function __remote_trace($url = '', $args = []){
	return __remote_request('TRACE', $url, $args);
}

/**
 * @return array
 */
function __sanitize_remote_args($args = []){
	if(!is_array($args)){
		$args = wp_parse_args($args);
	}
	if(__is_wp_http_request($args)){
		if(isset($args['timeout'])){
			$args['timeout'] = __sanitize_timeout($args['timeout']);
		}
		return $args;
	}
	return [
		'body' => $args,
	];
}

/**
 * @return int
 */
function __sanitize_timeout($timeout = 0){
	$timeout = (int) $timeout;
	if($timeout < 0){
		$timeout = 0;
	}
	$max_execution_time = (int) ini_get('max_execution_time');
	if(0 !== $max_execution_time){
		if(0 === $timeout or $timeout > $max_execution_time){
			$timeout = $max_execution_time - 1;
		}
	}
	if(__is_cloudflare()){
		if(0 === $timeout or $timeout > 98){
			$timeout = 98; // If the max_execution_time is set to greater than 98 seconds, reduce it a bit to prevent edge-case timeouts that may happen before the page is fully loaded. TODO: Check for Cloudflare Enterprise. See: https://developers.cloudflare.com/support/troubleshooting/cloudflare-errors/troubleshooting-cloudflare-5xx-errors/#error-524-a-timeout-occurred.
		}
	}
	return $timeout;
}
