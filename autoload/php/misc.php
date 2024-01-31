<?php

/**
 * @return void
 */
function __plugin_update_check($url = '', $file = ''){
	$url = wp_http_validate_url($url);
	if(!$url){
		return;
	}
	if(!$file){
		$file = __caller_file();
	}
	$plugin_file = __plugin_file($file);
	if(!$plugin_file){
		return '';
	}
	$slug = __plugin_slug(false, $file);
	$metadata_url = add_query_arg([
		'action' => 'get_metadata',
		'slug' => $slug,
	], $url);
	__check_for_updates($metadata_url, $plugin_file, $slug);
}

/**
 * @return void
 */
function __plugin_update_license($license = '', $file = ''){
	if(!$license){
		return;
	}
	if(!$file){
		$file = __caller_file();
	}
	$slug = __plugin_slug(false, $file);
	__set_update_license($slug, $license);
}
