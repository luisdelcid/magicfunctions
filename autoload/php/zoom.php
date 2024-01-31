<?php

/**
 * @return string|WP_Error
 */
function __zoom_access_token(){
    if(__isset_cache('zoom_access_token')){
        return (string) __get_cache('zoom_access_token', '');
    }
    $app_credentials = __zoom_app_credentials();
    if(is_wp_error($app_credentials)){
        return $app_credentials;
    }
    $authorization = base64_encode($app_credentials['client_id'] . ':' . $app_credentials['client_secret']);
    $url = 'https://zoom.us/oauth/token';
    $args = [
		'body' => [
			'account_id' => $app_credentials['account_id'],
			'grant_type' => 'account_credentials',
		],
		'headers' => [
			'Accept' => 'application/json',
			'Authorization' => 'Basic ' . $authorization,
			'Content-Type' => 'application/x-www-form-urlencoded',
		],
		'timeout' => 10,
	];
    $response = __remote_post($url, $args);
    if(is_wp_error($response)){
		return $response;
	}
	__set_cache('zoom_access_token', $response['access_token']);
	return $response['access_token'];
}

/**
 * @return string
 */
function __zoom_api_url($endpoint = ''){
	$base = 'https://api.zoom.us/v2';
    if(str_starts_with($endpoint, $base)){
        $endpoint = str_replace($base, '', $endpoint);
    }
	$endpoint = ltrim($endpoint, '/');
	$endpoint = untrailingslashit($endpoint);
	$endpoint = trailingslashit($base) . $endpoint;
	return $endpoint;
}

/**
 * @return array|WP_Error
 */
function __zoom_app_credentials($app_credentials = []){
    if(__isset_cache('zoom_app_credentials')){
        return (array) __get_cache('zoom_app_credentials', []);
    }
    $app_credentials = shortcode_atts([
        'account_id' => '',
        'client_id' => '',
        'client_secret' => '',
    ], $app_credentials);
    $missing = [];
    if(!$app_credentials['account_id']){
        $missing[] = 'Account ID';
    }
    if(!$app_credentials['client_id']){
        $missing[] = 'Client ID';
    }
    if(!$app_credentials['client_secret']){
        $missing[] = 'Client Secret';
    }
    if($missing){
		$message = sprintf(translate('Missing parameter(s): %s'), __implode_and($missing)) . '.';
		return __error($message);
	}
    __set_cache('zoom_app_credentials', $app_credentials);
    return $app_credentials;
}

/**
 * @return string|WP_Error
 */
function __zoom_oauth_token(){
    $transient = __str_prefix('zoom_oauth_token');
    $oauth_token = get_transient($transient);
    if($oauth_token){
        return $oauth_token;
    }
    $oauth_token = __zoom_access_token();
    if(is_wp_error($oauth_token)){
        return $oauth_token;
    }
    $expiration = 59 * MINUTE_IN_SECONDS; // The token’s time to live is 1 hour. https://developers.zoom.us/docs/internal-apps/s2s-oauth/
    set_transient($transient, $oauth_token, $expiration);
    return $oauth_token;
}

/**
 * @return array|WP_Error
 */
function __zoom_delete($endpoint = '', $args = [], $timeout = 10){
	return __zoom_request('DELETE', $endpoint, $args, $timeout);
}

/**
 * @return array|WP_Error
 */
function __zoom_get($endpoint = '', $args = [], $timeout = 10){
	return __zoom_request('GET', $endpoint, $args, $timeout);
}

/**
 * @return array|WP_Error
 */
function __zoom_patch($endpoint = '', $args = [], $timeout = 10){
	return __zoom_request('PATCH', $endpoint, $args, $timeout);
}

/**
 * @return array|WP_Error
 */
function __zoom_post($endpoint = '', $args = [], $timeout = 10){
	return __zoom_request('POST', $endpoint, $args, $timeout);
}

/**
 * @return array|WP_Error
 */
function __zoom_put($endpoint = '', $args = [], $timeout = 10){
	return __zoom_request('PUT', $endpoint, $args, $timeout);
}

/**
 * @return array|WP_Error
 */
function __zoom_request($method = '', $endpoint = '', $args = [], $timeout = 10){
	$oauth_token = __zoom_oauth_token();
	if(is_wp_error($oauth_token)){
		return $oauth_token;
	}
	$url = __zoom_api_url($endpoint);
	if(!is_array($args)){
		$args = wp_parse_args($args);
	}
	$args = [
		'body' => $args,
		'headers' => [
			'Accept' => 'application/json',
			'Authorization' => 'Bearer ' . $oauth_token,
			'Content-Type' => 'application/json',
		],
		'timeout' => $timeout,
	];
	return __remote_request($method, $url, $args);
}
