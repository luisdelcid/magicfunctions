<?php

/**
 * @return int
 */
function __absint($maybeint = 0){
	if(!is_numeric($maybeint)){
		return 0; // Make sure the value is numeric to avoid casting objects, for example, to int 1.
	}
	return absint($maybeint);
}

/**
 * @return array|null|string
 */
function __caller($index = 0, $element = ''){
	$index = __absint($index);
	$limit = $index + 1;
	$debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit);
    if($limit > count($debug)){
        return [];
    }
	$caller = shortcode_atts([
        'args' => [],
        'class' => '',
        'file' => '',
        'function' => '',
		'line' => 0,
        'object' => null,
        'type' => '',
    ], $debug[$index]);
	if(empty($element)){
		return $caller;
	}
	if(isset($caller[$element])){
		return $caller[$element];
	}
	return null;
}

/**
 * @return void|WP_Role
 */
function __clone_role($source = '', $destination = '', $display_name = ''){
	$role = get_role($source);
	if(is_null($role)){
		return;
	}
	$destination = __canonicalize($destination);
	return add_role($destination, $display_name, $role->capabilities);
}

/**
 * @return bool
 */
function __current_screen_in($ids = []){
	global $current_screen;
	if(!is_array($ids)){
		return false;
	}
	if(!isset($current_screen)){
		return false;
	}
	return in_array($current_screen->id, $ids);
}

/**
 * @return bool
 */
function __current_screen_is($id = ''){
	global $current_screen;
	if(!is_string($id)){
		return false;
	}
	if(!isset($current_screen)){
		return false;
	}
	return ($current_screen->id === $id);
}

/**
 * @return string
 */
function __format_function($function_name = '', $args = []){
	$str = '<span style="color: #24831d; font-family: monospace; font-weight: 400;">' . $function_name . '(';
	$function_args = [];
	foreach($args as $arg){
		$arg = shortcode_atts([
			'default' => 'null',
			'name' => '',
			'type' => '',
		], $arg);
		if($arg['default'] and $arg['name'] and $arg['type']){
			$function_args[] = '<span style="color: #cd2f23; font-family: monospace; font-style: italic; font-weight: 400;">' . $arg['type'] . '</span> <span style="color: #0f55c8; font-family: monospace; font-weight: 400;">$' . $arg['name'] . '</span> = <span style="color: #000; font-family: monospace; font-weight: 400;">' . $arg['default'] . '</span>';
		}
	}
	if($function_args){
		$str .= ' ' . implode(', ', $function_args) . ' ';
	}
	$str .= ')</span>';
	return $str;
}

/**
 * @return bool
 */
function __go_to($str = ''){
	return trim(str_replace('&larr;', '', sprintf(translate_with_gettext_context('&larr; Go to %s', 'site'), $str)));
}

/**
 * @return string
 */
function __breadcrumbs($breadcrumbs = [], $separator = '>'){
    $elements = [];
    foreach($breadcrumbs as $breadcrumb){
        if(!isset($breadcrumb['text'])){
            continue;
        }
        $text = $breadcrumb['text'];
        if(isset($breadcrumb['link'])){
            $href = $breadcrumb['link'];
            $target = isset($breadcrumb['target']) ? $breadcrumb['target'] : '_self';
            $element = sprintf('<a href="%1$s" target="%2$s">%3$s</a>', esc_url($href), esc_attr($target), esc_html($text));
        } else {
            $element = sprintf('<span>%1$s</a>', esc_html($text));
        }
        $elements[] = $element;
    }
    $separator = ' ' . trim($separator) . ' ';
	return implode($separator, $elements);
}

/**
 * @return bool
 */
function __has_btn_class($class = ''){
    $class = __remove_whitespaces($class);
    preg_match_all('/btn-[A-Za-z][-A-Za-z0-9_:.]*/', $class, $matches);
	$matches = array_filter($matches[0], function($match){
		return !in_array($match, ['btn-block', 'btn-lg', 'btn-sm']);
	});
	return (bool) $matches;
}

/**
 * @return bool
 */
function __is_doing_heartbeat(){
	return (wp_doing_ajax() and isset($_POST['action']) and 'heartbeat' === $_POST['action']);
}

/**
 * @return bool
 */
function __is_false($data = ''){
	return in_array((string) $data, ['0', 'false', 'off'], true);
}

/**
 * @return bool
 */
function __is_revision_or_auto_draft($post = null){
	return (wp_is_post_revision($post) or 'auto-draft' === get_post_status($post));
}

/**
 * @return bool
 */
function __is_true($data = ''){
	return in_array((string) $data, ['1', 'on', 'true'], true);
}

/**
 * @return array
 */
function __post_type_labels($singular = '', $plural = '', $all = true){
	if(empty($singular)){
		return [];
	}
	if(empty($plural)){
		$plural = $singular;
	}
	return [
		'name' => $plural,
		'singular_name' => $singular,
		'add_new' => 'Add New',
		'add_new_item' => 'Add New ' . $singular,
		'edit_item' => 'Edit ' . $singular,
		'new_item' => 'New ' . $singular,
		'view_item' => 'View ' . $singular,
		'view_items' => 'View ' . $plural,
		'search_items' => 'Search ' . $plural,
		'not_found' => 'No ' . strtolower($plural) . ' found.',
		'not_found_in_trash' => 'No ' . strtolower($plural) . ' found in Trash.',
		'parent_item_colon' => 'Parent ' . $singular . ':',
		'all_items' => ($all ? 'All ' : '') . $plural,
		'archives' => $singular . ' Archives',
		'attributes' => $singular . ' Attributes',
		'insert_into_item' => 'Insert into ' . strtolower($singular),
		'uploaded_to_this_item' => 'Uploaded to this ' . strtolower($singular),
		'featured_image' => 'Featured image',
		'set_featured_image' => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image' => 'Use as featured image',
		'filter_items_list' => 'Filter ' . strtolower($plural) . ' list',
		'items_list_navigation' => $plural . ' list navigation',
		'items_list' => $plural . ' list',
		'item_published' => $singular . ' published.',
		'item_published_privately' => $singular . ' published privately.',
		'item_reverted_to_draft' => $singular . ' reverted to draft.',
		'item_scheduled' => $singular . ' scheduled.',
		'item_updated' => $singular . ' updated.',
	];
}

/**
 * @return void
 */
function __test(){
	__exit_with_error('Hello, World!');
}

/**
 * @return string
 */
function __validate_redirect_to($url = ''){
	$redirect_to = isset($_REQUEST['redirect_to']) ? wp_http_validate_url($_REQUEST['redirect_to']) : false;
	if(!$redirect_to and !empty($url)){
		$redirect_to = wp_http_validate_url($url);
	}
	return (string) $redirect_to;
}
