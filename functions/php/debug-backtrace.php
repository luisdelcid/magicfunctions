<?php

/**
 * @return mixed
 */
function __backtrace($element = '', $index = 0){
	$index = __absint($index) + 1;
	$limit = $index + 1;
    switch($element){
        case 'args':
            $debug = debug_backtrace(0, $limit);
            break;
        case 'object':
            $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS|DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit);
            break;
        default:
            $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit);
    }
    $pairs = [
        'args' => [],
        'class' => '',
        'file' => '',
        'function' => '',
		'line' => 0,
        'object' => null,
        'type' => '',
    ];
    if($limit > count($debug)){
        $caller = $pairs;
    } else {
        $caller = shortcode_atts($pairs, $debug[$index]);
    }
	if(empty($element)){
		return $caller;
	}
	if(isset($caller[$element])){
		return $caller[$element];
	}
	return false;
}
