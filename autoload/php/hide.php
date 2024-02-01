<?php

/**
 * @return void
 */
function __add_hiding_rule($args = []){
    if(is_multisite()){
		return; // The rewrite rules are not for WordPress MU networks.
	}
	$pairs = [
        'capability' => '',
        'exclude' => [],
        'file' => '',
		'subdir' => '',
        'type' => 0,
	];
    $args = shortcode_atts($pairs, $args);
	$md5 = __md5($args);
    if(__isset_array_cache('hide_uploads_subdir', $md5)){
        return; // Prevent adding rule when already added.
    }
    __set_array_cache('hide_uploads_subdir', $md5, $args);
	$uploads_use_yearmonth_folders = false;
	$subdir = ltrim(untrailingslashit($args['subdir']), '/');
	if($subdir){
		$subdir = '/(' . $subdir . ')';
	} else {
		if(get_option('uploads_use_yearmonth_folders')){
			$subdir = '/(\d{4})/(\d{2})';
			$uploads_use_yearmonth_folders = true;
		} else {
			$subdir = '';
		}
	}
	$upload_dir = wp_get_upload_dir();
	if($upload_dir['error']){
		return;
	}
    $path = plugin_dir_path(__file()) . 'shortinit/php/readfile.php';
	$tmp = str_replace(wp_normalize_path(ABSPATH), '', wp_normalize_path($path));
	$parts = explode('/', $tmp);
	$levels = count($parts);
	$query = __dir_to_url($path);
	$regex = $upload_dir['baseurl'] . $subdir. '/(.+)';
	if($uploads_use_yearmonth_folders){
		$atts['yyyy'] = '$1';
		$atts['mm'] = '$2';
		$atts['file'] = '$3';
	} else {
		$atts['subdir'] = '$1';
		$atts['file'] = '$2';
	}
	$atts['levels'] = $levels;
    $atts['md5'] = $md5;
    switch($args['type']){
        case 1:
            $atts['type'] = 1;
            break;
        case 2:
            $atts['capability'] = $args['capability'];
            $atts['type'] = 2;
            break;
        case 3:
            $atts['type'] = 3;
            break;
        default:
            return;
    }
    $option = __str_prefix('hide_uploads_subdir_exclude_' . $md5);
    update_option($option, (array) $args['exclude'], 'no');
	$query = add_query_arg($atts, $query);
	__add_external_rule($regex, $query, $args['file']);
}

/**
 * @return void
 */
function __hide_the_dashboard($capability = 'edit_posts', $location = ''){
    __set_cache('hide_the_dashboard', [
        'capability' => $capability,
		'location' => $location,
    ]);
    __add_action_once('admin_init', '__maybe_hide_the_dashboard');
}

/**
 * @return void
 */
function __hide_the_toolbar($capability = 'edit_posts'){
    __set_cache('hide_the_toolbar', [
        'capability' => $capability,
    ]);
    __add_filter_once('show_admin_bar', '__maybe_hide_the_toolbar');
}

/**
 * @return void
 */
function __hide_others_media($capability = 'edit_others_posts'){
    __set_cache('hide_others_media', [
        'capability' => $capability,
    ]);
    __add_filter_once('ajax_query_attachments_args', '__maybe_hide_others_media');
}

/**
 * @return void
 */
function __hide_others_posts($capability = 'edit_others_posts'){
    __set_cache('hide_others_posts', [
        'capability' => $capability,
    ]);
    __add_action_once('current_screen', '__maybe_hide_others_posts_count');
    __add_action_once('pre_get_posts', '__maybe_hide_others_posts_query_args');
}

/**
 * @return void
 */
function __hide_the_entire_site($capability = 'read', $exclude_other_pages = [], $exclude_special_pages = []){
    __set_cache('hide_the_entire_site', [
        'capability' => $capability,
        'exclude_other_pages' => $exclude_other_pages,
		'exclude_special_pages' => $exclude_special_pages,
    ]);
    __add_action_once('template_redirect', '__maybe_hide_the_entire_site');
}

/**
 * @return void
 */
function __hide_the_rest_api($capability = 'read'){
    __set_cache('hide_the_rest_api', [
        'capability' => $capability,
    ]);
    __add_filter_once('rest_authentication_errors', '__maybe_hide_the_rest_api');
}

/**
 * @return void
 */
function __hide_uploads_subdir($subdir = '', $exclude = [], $file = ''){
    $args = [
        'exclude' => $exclude,
        'subdir' => $subdir,
        'type' => 1,
    ];
    if(!$file){
        $file = __backtrace('file');
	}
    $args['file'] = $file;
    return __add_hiding_rule($args);
}

/**
 * @return void
 */
function __hide_uploads_subdir_by_capability($subdir = '', $capability = 'read', $exclude = [], $file = ''){
    $args = [
        'capability' => $capability,
        'exclude' => $exclude,
        'subdir' => $subdir,
        'type' => 2,
    ];
    if(!$file){
		$file = __backtrace('file');
	}
    $args['file'] = $file;
    return __add_hiding_rule($args);
}

/**
 * @return void
 */
function __hide_uploads_subdir_by_status($subdir = '', $exclude = [], $file = ''){
    $args = [
        'exclude' => $exclude,
        'subdir' => $subdir,
        'type' => 3,
    ];
    if(!$file){
		$file = __backtrace('file');
	}
    $args['file'] = $file;
    return __add_hiding_rule($args);
}

/**
 * @return void
 */
function __hide_wp(){
    __local_login_header();
    __set_cache('hide_wp', true);
	__add_action_once('admin_init', '__maybe_hide_wp_from_admin');
    __add_action_once('wp_before_admin_bar_render', '__maybe_hide_wp_from_admin_bar');
}

/**
 * @return void
 */
function __local_login_header(){
    __set_cache('local_login_header', true);
    __add_filter_once('login_headertext', '__maybe_local_login_headertext');
    __add_filter_once('login_headerurl', '__maybe_local_login_headerurl');
}

/**
 * @return void
 */
function __maybe_hide_the_dashboard(){
    $hide_the_dashboard = (array) __get_cache('hide_the_dashboard', []);
	if(!$hide_the_dashboard){
		return;
	}
    if(wp_doing_ajax()){
        return;
    }
    if(current_user_can($hide_the_dashboard['capability'])){
        return;
    }
    $location = wp_validate_redirect($hide_the_dashboard['location'], home_url());
	wp_safe_redirect($location);
	exit;
}

/**
 * @return bool
 */
function __maybe_hide_the_toolbar($show){
    $hide_the_toolbar = (array) __get_cache('hide_the_toolbar', []);
	if(!$hide_the_toolbar){
		return $show;
	}
    if(current_user_can($hide_the_toolbar['capability'])){
        return $show;
    }
    $show = false;
    return $show;
}

/**
 * @return array
 */
function __maybe_hide_others_media($query){
    $hide_others_media = (array) __get_cache('hide_others_media', []);
	if(!$hide_others_media){
		return;
	}
    if(current_user_can($hide_others_media['capability'])){
        return $query;
    }
    $query['author'] = get_current_user_id();
    return $query;
}

/**
 * @return void
 */
function __maybe_hide_others_posts_count(){
    global $current_screen, $pagenow;
    $hide_others_posts = (array) __get_cache('hide_others_posts', []);
	if(!$hide_others_posts){
		return;
	}
    if('edit.php' !== $pagenow){
        return;
    }
    if(current_user_can($hide_others_posts['capability'])){
		return;
	}
    __add_filter_once('views_' . $current_screen->id, '__maybe_hide_others_posts_count_from_views');
}

/**
 * @return array
 */
function __maybe_hide_others_posts_count_from_views($views){
    //$screen_id = str_replace('views_', '', current_filter());
    foreach($views as $index => $view){
        $views[$index] = preg_replace('/ <span class="count">\([0-9]+\)<\/span>/', '', $view);
    }
	return $views;
}

/**
 * @return array
 */
function __maybe_hide_others_posts_query_args($query){
    global $pagenow;
    $hide_others_posts = (array) __get_cache('hide_others_posts', []);
	if(!$hide_others_posts){
		return;
	}
    if('edit.php' !== $pagenow){
        return;
    }
    if(current_user_can($hide_others_posts['capability'])){
		return;
	}
    $query->set('author', get_current_user_id());
    return $query;
}

/**
 * @return void
 */
function __maybe_hide_the_entire_site(){
    $hide_the_entire_site = (array) __get_cache('hide_the_entire_site', []);
	if(!$hide_the_entire_site){
		return;
	}
    $exclude_other_pages = in_array(get_the_ID(), (array) $hide_the_entire_site['exclude_other_pages']);
	$exclude_special_pages = ((is_front_page() and in_array('front_end', (array) $hide_the_entire_site['exclude_special_pages'])) or (is_home() and in_array('home', (array) $hide_the_entire_site['exclude_special_pages'])));
    if($exclude_other_pages or $exclude_special_pages){
        return;
    }
    if(!is_user_logged_in()){
        auth_redirect();
    }
    if(current_user_can($hide_the_entire_site['capability'])){
        return;
    }
    __exit_with_error(translate('Sorry, you are not allowed to access this page.'), translate('You need a higher level of permission.'), 403);
}

/**
 * @return null|WP_Error
 */
function __maybe_hide_the_rest_api($errors){
    $hide_the_rest_api = (array) __get_cache('hide_the_rest_api', []);
	if(!$hide_the_rest_api){
		return $errors;
	}
    if(current_user_can($hide_the_rest_api['capability'])){
        return $errors;
    }
    // TODO: is_wp_error($errors) ? $errors->add() : new \WP_Error
    return __error(translate('You need a higher level of permission.'), [
		'status' => 401,
	]);
}

/**
 * @return void
 */
function __maybe_hide_wp_from_admin(){
    $hide_wp = (bool) __get_cache('hide_wp', false);
	if(!$hide_wp){
		return;
	}
    remove_action('welcome_panel', 'wp_welcome_panel');
}

/**
 * @return void
 */
function __maybe_hide_wp_from_admin_bar(){
    global $wp_admin_bar;
    $hide_wp = (bool) __get_cache('hide_wp', false);
	if(!$hide_wp){
		return;
	}
	$wp_admin_bar->remove_node('wp-logo');
}

/**
 * @return string
 */
function __maybe_local_login_headertext($login_header_text){
	$local_login_header = (bool) __get_cache('local_login_header', false);
	if(!$local_login_header){
        return $login_header_text;
	}
	return get_option('blogname');
}

/**
 * @return string
 */
function __maybe_local_login_headerurl($login_header_url){
    $local_login_header = (bool) __get_cache('local_login_header', false);
	if(!$local_login_header){
        return $login_header_url;
	}
    return home_url();
}
