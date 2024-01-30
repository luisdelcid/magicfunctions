<?php

/**
 * Alias for current_time.
 *
 * Differs from current_time in that it will always return a string.
 *
 * If 'offset_or_tz' parameter is an empty string, the output is adjusted with the GMT offset in the WordPress option.
 *
 * @return string
 */
function __current_time($type = 'U', $offset_or_tz = ''){
	if('timestamp' === $type){
		$type = 'U';
	}
	if('mysql' === $type){
		$type = 'Y-m-d H:i:s';
	}
	$timezone = $offset_or_tz ? __timezone($offset_or_tz) : wp_timezone();
	$datetime = new \DateTime('now', $timezone);
	return $datetime->format($type);
}

/**
 * @return string
 */
function __date_convert($string = '', $fromtz = '', $totz = '', $format = 'Y-m-d H:i:s'){
	$datetime = date_create($string, __timezone($fromtz));
	if(false === $datetime){
		return gmdate($format, 0);
	}
	return $datetime->setTimezone(__timezone($totz))->format($format);
}

/**
 * @return bool|string
 */
function __is_mysql_date($subject = ''){
	$pattern = '/^\d{4}\-(0[1-9]|1[0-2])\-(0[1-9]|[12]\d|3[01]) ([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/';
	if(!preg_match($pattern, $subject)){
		return false;
	}
	return $subject;
}

/**
 * @param string $offset_or_tz Optional. Default GMT offset or timezone string. Must be either a valid offset (-12 to 14) or a valid timezone string.
 *
 * @return array
 */
function __offset_or_tz($offset_or_tz = ''){
	if(is_numeric($offset_or_tz)){
		return [
			'gmt_offset' => $offset_or_tz,
			'timezone_string' => '',
		];
	}
	// Map UTC+- timezones to gmt_offsets and set timezone_string to empty.
	if(preg_match('/^UTC[+-]/', $offset_or_tz)){
		return [
			'gmt_offset' => (int) preg_replace('/UTC\+?/', '', $offset_or_tz),
			'timezone_string' => '',
		];
	}
	if(in_array($offset_or_tz, timezone_identifiers_list())){
		return [
			'gmt_offset' => 0,
			'timezone_string' => $offset_or_tz,
		];
	}
	return [
		'gmt_offset' => 0,
		'timezone_string' => 'UTC',
	];
}

/**
 * @return DateTimeZone
 */
function __timezone($offset_or_tz = ''){
	return new \DateTimeZone(__timezone_string($offset_or_tz));
}

/**
 * @return string
 */
function __timezone_string($offset_or_tz = ''){
	$offset_or_tz = __offset_or_tz($offset_or_tz);
	$timezone_string = $offset_or_tz['timezone_string'];
	if($timezone_string){
		return $timezone_string;
	}
	$offset = (float) $offset_or_tz['gmt_offset'];
	$hours = (int) $offset;
	$minutes = ($offset - $hours);
	$sign = ($offset < 0) ? '-' : '+';
	$abs_hour = abs($hours);
	$abs_mins = abs($minutes * 60);
	$tz_offset = sprintf('%s%02d:%02d', $sign, $abs_hour, $abs_mins);
	return $tz_offset;
}
