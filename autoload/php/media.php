<?php

/**
 * @return void
 */
function __add_image_size($name = '', $width = 0, $height = 0, $crop = false){
	$image_sizes = get_intermediate_image_sizes();
	$size = sanitize_title($name);
	if(in_array($size, $image_sizes)){
		return; // Does NOT overwrite.
	}
	add_image_size($size, $width, $height, $crop);
	__add_filter_once('image_size_names_choose', '__maybe_add_image_size_names');
	__set_array_cache('image_sizes', $size, $name);
}

/**
 * @return int
 */
function __attachment_url_to_postid($url = ''){
	$post_id = __guid_to_postid($url);
	if($post_id){
		return $post_id;
	}
	preg_match('/^(.+)(\-\d+x\d+)(\.' . substr($url, strrpos($url, '.') + 1) . ')?$/', $url, $matches); // Resized.
	if($matches){
		$url = $matches[1];
		if(isset($matches[3])){
			$url .= $matches[3];
		}
		$post_id = __guid_to_postid($url);
		if($post_id){
			return $post_id;
		}
	}
	preg_match('/^(.+)(\-scaled)(\.' . substr($url, strrpos($url, '.') + 1) . ')?$/', $url, $matches); // Scaled.
	if($matches){
		$url = $matches[1];
		if(isset($matches[3])){
			$url .= $matches[3];
		}
		$post_id = __guid_to_postid($url);
		if($post_id){
			return $post_id;
		}
	}
	preg_match('/^(.+)(\-e\d+)(\.' . substr($url, strrpos($url, '.') + 1) . ')?$/', $url, $matches); // Edited.
	if($matches){
		$url = $matches[1];
		if(isset($matches[3])){
			$url .= $matches[3];
		}
		$post_id = __guid_to_postid($url);
		if($post_id){
			return $post_id;
		}
	}
	return 0;
}

/**
 * @return array
 */
function __convert_exts_to_mimes($exts = []){
    if(empty($exts)){
        $exts = array_merge(wp_get_audio_extensions(), wp_get_video_extensions(), __image_extensions());
    }
    $mimes = wp_get_mime_types();
    $ext_mimes = [];
    foreach($exts as $ext){
        foreach($mimes as $ext_preg => $mime_match){
            if(preg_match('#' . $ext . '#i', $ext_preg)){
                $ext_mimes[$ext] = $mime_match;
                break;
            }
        }
    }
    return $ext_mimes;
}

/**
 * @return string
 */
function __fa_file_type($post = null){
	if('attachment' !== get_post_status($post)){
		return '';
	}
	if(wp_attachment_is('audio', $post)){
		return 'audio';
	}
	if(wp_attachment_is('image', $post)){
		return 'image';
	}
	if(wp_attachment_is('video', $post)){
		return 'video';
	}
	$type = get_post_mime_type($post);
	switch($type){
		case 'application/zip':
		case 'application/x-rar-compressed':
		case 'application/x-7z-compressed':
		case 'application/x-tar':
			return 'archive';
			break;
		case 'application/vnd.ms-excel':
		case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
			return 'excel';
			break;
		case 'application/pdf':
			return 'pdf';
			break;
		case 'application/vnd.ms-powerpoint':
		case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
			return 'powerpoint';
			break;
		case 'application/msword':
		case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
			return 'word';
			break;
		default:
			return 'file';
	}
}

/**
 * @return int
 */
function __guid_to_postid($guid = '', $check_rewrite_rules = false){
	global $wpdb;
	$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid = %s", $guid);
	$post_id = $wpdb->get_var($query);
	if(null !== $post_id){
		return intval($post_id);
	}
	if($check_rewrite_rules){
		return url_to_postid($guid);
	}
	return 0;
}

/**
 * @return array
 */
function __image_extensions(){
	$image_extensions = ['jpg', 'jpeg', 'jpe', 'png', 'gif', 'bmp', 'tiff', 'tif', 'webp', 'ico', 'heic'];
    return $image_extensions;
}

/**
 * This function’s access is marked private. This means it is not intended for use by plugin or theme developers, only in other core functions.
 *
 * This function MUST be called inside the 'image_size_names_choose' filter hook.
 *
 * @return array
 */
function __maybe_add_image_size_names($sizes){
	if(!doing_filter('image_size_names_choose')){
        return $sizes;
    }
	$image_sizes = (array) __get_cache('image_sizes', []);
	foreach($image_sizes as $size => $name){
		$sizes[$size] = $name;
	}
	return $sizes;
}

/**
 * @return bool
 */
function __maybe_generate_attachment_metadata($attachment_id = 0){
	$attachment = get_post($attachment_id);
	if(null === $attachment){
		return false;
	}
	if('attachment' !== $attachment->post_type){
		return false;
	}
	wp_raise_memory_limit('image');
	if(!function_exists('wp_generate_attachment_metadata')){
		require_once(ABSPATH . 'wp-admin/includes/image.php');
	}
	wp_maybe_generate_attachment_metadata($attachment);
	return true;
}
