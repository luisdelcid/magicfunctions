<?php

/**
 * @return WP_Query
 */
function __get_posts_query($args = []){
	$defaults = [
		'category' => 0,
		'exclude' => [],
		'include' => [],
		'meta_key' => '',
		'meta_value' => '',
		'numberposts' => 5,
		'order' => 'DESC',
		'orderby' => 'date',
		'post_type' => 'post',
		'suppress_filters' => true,
	];
	$parsed_args = wp_parse_args($args, $defaults);
	if(empty($parsed_args['post_status'])){
		$parsed_args['post_status'] = ('attachment' === $parsed_args['post_type']) ? 'inherit' : 'publish';
	}
	if(!empty($parsed_args['numberposts']) and empty($parsed_args['posts_per_page'])){
		$parsed_args['posts_per_page'] = $parsed_args['numberposts'];
	}
	if(!empty($parsed_args['category'])){
		$parsed_args['cat'] = $parsed_args['category'];
	}
	if(!empty($parsed_args['include'])){
		$incposts = wp_parse_id_list($parsed_args['include']);
		$parsed_args['posts_per_page'] = count($incposts);  // Only the number of posts included.
		$parsed_args['post__in'] = $incposts;
	} elseif(!empty($parsed_args['exclude'])){
		$parsed_args['post__not_in'] = wp_parse_id_list($parsed_args['exclude']);
	}
	$parsed_args['ignore_sticky_posts'] = true;
	$parsed_args['no_found_rows'] = true;
	$query = new \WP_Query;
	$query->query($parsed_args);
	return $query;
}

/**
 * Alias for wp_get_current_user, get_user_by, get_userdata.
 *
 * @return bool|WP_User
 */
function __get_user($user = null){
    if(is_null($user)){
        return (is_user_logged_in() ? wp_get_current_user() : false);
	}
    if($user instanceof \WP_User){
        return $user->exists() ? $user : false;
    }
    if(is_numeric($user)){
        return get_userdata($user);
    }
    if(!is_string($user)){
        return false;
    }
    if(username_exists($user)){
        return get_user_by('login', $user);
    }
    if(!is_email($user)){
        return false;
    }
    return get_user_by('email', $email);
}

/**
 * @return WP_User_Query
 */
function __get_users_query($args = []){
    $defaults = [
        'count_total' => false,
    ];
    $parsed_args = wp_parse_args($args, $defaults);
    $query = new \WP_User_Query($parsed_args);
    return $query;
}
