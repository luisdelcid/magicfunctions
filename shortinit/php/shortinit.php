<?php

/**
 * @return void
 */
function __404(){
    status_header(404);
	die('404 &#8212; File not found.');
}

/**
 * @return int
 */
function __attachment_file_to_postid($file = ''){
	if(!defined('WP_CONTENT_URL')){
		define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
	}
    $upload_dir = wp_get_upload_dir();
    $url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file);
    return __attachment_url_to_postid($url);
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
 * @return string
 */
function __canonicalize($key = ''){
	$key = sanitize_title($key);
	$key = str_replace('-', '_', $key);
	return $key;
}

/**
 * @return bool
 */
function __current_user_can($capability = ''){
    $user_id = __get_current_user_id();
    return __user_can($user_id, $capability);
}

/**
 * @return string
 */
function __dirname($path = '', $levels = 1){
    $levels = (int) $levels;
    $path = dirname($path);
    if($levels > 1){
        $levels --;
        return __dirname($path, $levels);
    } else {
        return $path;
    }
}

/**
 * @return int
 */
function __get_current_user_id(){
    global $wpdb;
    $siteurl = get_option('siteurl');
    if(!$siteurl){
        return 0;
    }
    $cookie_hash = 'wordpress_logged_in_' . md5($siteurl);
    if(!isset($_COOKIE[$cookie_hash])){
        return 0;
    }
    $cookie = $_COOKIE[$cookie_hash];
    $cookie_parts = explode('|', $cookie); // 0 => user_login, 1 => expiration, 2 => token, 3 => hmac
    if(4 !== count($cookie_parts)){ // Check if the cookie has the correct number of parts, if not then we can't be sure that $cookie_parts[0] is the user name.
        return 0;
    }
    $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_login = %s LIMIT 1", $cookie_parts[0]));
    if(!$user){
        return;
    }
    return $user->ID;
}

/**
 * @return object
 */
function __get_post($post_id = 0){
    global $wpdb;
	$query = $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d", $post_id);
	return $wpdb->get_row($query);
}

/**
 * @return array|null|string
 */
function __get_post_meta($post_id = 0, $key = '', $single = false){
    global $wpdb;
    $query = $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $post_id, $key);
    if($single){
        return $wpdb->get_var($query);
    } else {
        return $wpdb->get_col($query);
    }
}

/**
 * @return bool|string
 */
function __get_post_status($post_id = 0){
	$post = __get_post($post_id);
	if(!is_object($post)){
		return false;
	}
	$post_status = $post->post_status;
	if('attachment' === $post->post_type and 'inherit' === $post_status){
		if(0 === $post->post_parent or !__get_post($post->post_parent) or $post->ID === $post->post_parent){
			$post_status = 'publish'; // Unattached attachments with inherit status are assumed to be published.
		} elseif('trash' === __get_post_status($post->post_parent)){
			$post_status = __get_post_meta($post->post_parent, '_wp_trash_meta_status', true); // Get parent status prior to trashing.
			if(!$post_status){
				$post_status = 'publish'; // Assume publish as above.
			}
		} else {
			$post_status = __get_post_status($post->post_parent);
		}
	} elseif('attachment' === $post->post_type and !in_array($post_status, ['auto-draft', 'private', 'trash'], true)){
		$post_status = 'publish'; // Ensure uninherited attachments have a permitted status either 'private', 'trash', 'auto-draft'. This is to match the logic in wp_insert_post().
	}
	return $post_status;
}

/**
 * @return int
 */
function __guid_to_postid($guid = ''){
    global $wpdb;
    $query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid = %s", $guid);
    $post_id = $wpdb->get_var($query);
    if(null === $post_id){
        return 0;
    }
    return (int) $post_id;
}

/**
 * @return string
 */
function __remove_whitespaces($str = ''){
	return trim(preg_replace('/[\r\n\t ]+/', ' ', $str));
}

/**
 * @return void
 */
function __serve_file($file = ''){
    $mime = wp_check_filetype($file);
	if(false === $mime['type'] and function_exists('mime_content_type')){
		$mime['type'] = mime_content_type($file);
	}
	if($mime['type']){
		$mimetype = $mime['type'];
	} else {
		$mimetype = 'image/' . substr($file, strrpos($file, '.') + 1);
	}
	header('Content-Type: ' . $mimetype); // Always send this.
	if(false === strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS')){
		header('Content-Length: ' . filesize($file));
	}
	readfile($file); // If we made it this far, just serve the file.
	flush();
	die;
}

/**
 * @return string
 */
function __str_prefix($str = '', $prefix = ''){
    $prefix = str_replace('\\', '_', $prefix); // Fix namespaces.
    $prefix = __canonicalize($prefix);
    $prefix = rtrim($prefix, '_');
    if(empty($prefix)){
        $prefix = __prefix();
    }
    $str = __remove_whitespaces($str);
    if(empty($str)){
        return $prefix;
    }
    if(0 === strpos($str, $prefix)){
        return $str; // Text is already prefixed.
    }
    return $prefix . '_' . $str;
}

/**
 * @return string
 */
function __str_slug($str = '', $slug = ''){
    $slug = str_replace('_', '-', $slug); // Fix canonicalized.
    $slug = str_replace('\\', '-', $slug); // Fix namespaces.
	$slug = sanitize_title($slug);
    $slug = rtrim($slug, '-');
    if(!$slug){
        $slug = __slug();
    }
    $str = __remove_whitespaces($str);
    if(empty($str)){
        return $slug;
    }
    if(0 === strpos($str, $slug)){
        return $str; // Text is already slugged.
    }
    return $slug . '-' . $str;
}

/**
 * @return void
 */
function __test(){
    status_header(500);
	die('Hello, World!');
}

/**
 * @return bool
 */
function __user_can($user_id = 0, $capability = ''){
    global $wpdb;
    $query = $wpdb->prepare("SELECT meta_value FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = %s LIMIT 1", $user_id, $wpdb->prefix . 'capabilities');
    $roles = get_option($wpdb->prefix . 'user_roles');
    $user_capabilities = [];
    $user_roles = $wpdb->get_var($query);
	$user_roles = maybe_unserialize($user_roles);
	$user_roles = (array) $user_roles;
    foreach(array_keys($user_roles) as $user_role){
    	if(isset($roles[$user_role])){
            foreach(array_keys($roles[$user_role]['capabilities']) as $role_capability){
                if(!in_array($role_capability, $user_capabilities)){
                    $user_capabilities[] = $role_capability;
                }
            }
    	} else {
            if(!in_array($user_role, $user_capabilities)){
                $user_capabilities[] = $user_role;
            }
    	}
    }
    return in_array($capability, $user_capabilities);
}
