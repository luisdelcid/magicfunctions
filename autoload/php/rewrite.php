<?php

/**
 * @return void
 */
function __add_external_rule($regex = '', $query = '', $plugin_file = ''){
	$rule = [
		'plugin_file' => $plugin_file,
		'query' => str_replace(site_url('/'), '', $query),
		'regex' => str_replace(site_url('/'), '', $regex),
	];
	$md5 = __md5($rule);
	__set_array_cache('external_rules', $md5, $rule);
    __add_action_once('admin_init', '__maybe_add_external_rules_notice');
    __add_action_once('generate_rewrite_rules', '__maybe_add_external_rules');
}

/**
 * @return bool
 */
function __external_rule_exists($regex = '', $query = ''){
    if(__isset_cache('rewrite_rules')){
        $rewrite_rules = __get_cache('rewrite_rules');
    } else {
        $rewrite_rules = array_filter(extract_from_markers(get_home_path() . '.htaccess', 'WordPress'));
        __set_cache('rewrite_rules', $rewrite_rules);
    }
	$regex = str_replace('.+?', '.+', $regex); // Apache 1.3 does not support the reluctant (non-greedy) modifier.
	$rule = 'RewriteRule ^' . $regex . ' ' . __home_root() . $query . ' [QSA,L]';
	return in_array($rule, $rewrite_rules);
}

/**
 * @return string
 */
function __home_root(){
	$home_root = parse_url(home_url());
	if(isset($home_root['path'])){
		$home_root = trailingslashit($home_root['path']);
	} else {
		$home_root = '/';
	}
	return $home_root;
}

/**
 * @return void
 */
function __maybe_add_external_rules($wp_rewrite){
	$external_rules = (array) __get_cache('external_rules', []);
    if(!$external_rules){
        return;
    }
    foreach($external_rules as $rule){
        if($rule['plugin_file'] and __is_plugin_deactivating($rule['plugin_file'])){
            continue;
        }
        $wp_rewrite->add_external_rule($rule['regex'], $rule['query']);
    }
}

/**
 * @return void
 */
function __maybe_add_external_rules_notice(){
	if(!current_user_can('manage_options')){
		return;
	}
	$external_rules = (array) __get_cache('external_rules', []);
    if(!$external_rules){
        return;
    }
    $add_admin_notice = false;
	foreach($external_rules as $rule){
		if(!__external_rule_exists($rule['regex'], $rule['query'])){
			$add_admin_notice = true;
			break;
		}
	}
	if(!$add_admin_notice){
        return;
	}
    $message = sprintf(translate('You should update your %s file now.'), '<code>.htaccess</code>');
    $message .= ' ';
    $message .= sprintf('<a href="%s">%s</a>', esc_url(admin_url('options-permalink.php')), translate('Flush permalinks')) . '.';
    __add_admin_notice($message);
}
