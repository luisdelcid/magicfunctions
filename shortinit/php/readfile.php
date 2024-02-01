<?php

require_once(rtrim(dirname(__FILE__), '/\\') . '/hardcoded.php');
require_once(rtrim(dirname(__FILE__), '/\\') . '/shortinit.php');
if(!isset($_GET['file'], $_GET['levels'], $_GET['md5'], $_GET['type'])){
	__404();
}
$abspath = __dirname(__FILE__, $_GET['levels']);
$loader = $abspath . '/wp-load.php';
if(!file_exists($loader)){
    __404();
}
define('SHORTINIT', true);
require_once($loader);
error_reporting(0);
nocache_headers();
$basedir = ABSPATH . 'wp-content/uploads';
$subdir = isset($_GET['yyyy'], $_GET['mm']) ? '/' . $_GET['yyyy'] . '/' . $_GET['mm'] : (isset($_GET['subdir']) ? '/' . $_GET['subdir'] : '');
$file = $basedir . $subdir . '/' . $_GET['file'];
if(!is_file($file)){
	__404();
}
$post_id = __attachment_file_to_postid($file);
if(!$post_id){
    __404($file);
}
$option = __str_prefix('hide_uploads_subdir_exclude_' . $_GET['md5']);
$exclude = (array) get_option($option, []);
if($exclude and in_array($post_id, $exclude)){
	__serve_file($file);
}
$user_id = __get_current_user_id();
if(!$user_id){
    __404();
}
switch($_GET['type']){
	case 1: // Logged-in users.
		__serve_file($file);
		break;
	case 2: // User capabilities.
		$post = __get_post($post_id);
		if($user_id === $post->post_author){
			__serve_file($file);
		}
		$capability = isset($_GET['capability']) ? $_GET['capability'] : 'read';
		if(__current_user_can($capability)){
			__serve_file($file);
		}
		break;
	case 3: // Private posts.
		$post_status = __get_post_status($post_id);
		if('private' !== $post_status){
			__serve_file($file);
		}
		$post = __get_post($post_id);
		if($user_id === $post->post_author){
			__serve_file($file);
		}
		if(__current_user_can('read_private_posts')){
			__serve_file($file);
		}
		break;
}
__404();
