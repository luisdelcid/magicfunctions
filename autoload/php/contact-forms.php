<?php

/**
 * This function MUST be called inside the 'wpcf7_before_send_mail' action hook.
 *
 * @return void
 */
function __cf7_abort(&$abort, $message = '', $submission = null){
	if(!doing_action('wpcf7_before_send_mail')){
        return; // Too early or too late.
    }
	if($abort){
		return; // Already aborted.
	}
    $submission = __cf7_submission($submission);
    if(is_null($submission)){
        return;
    }
	if(!$submission->is('init')){
		return; // Avoid conflicts with other statuses.
	}
    $abort = true; // Avoid mail_sent and mail_failed action hooks.
	if(empty($message)){
		$message = translate('Sending mail has been aborted.', 'contact-form-7');
	}
	$message = __cf7_message($message, 'aborted');
    $submission->set_response($message);
    $submission->set_status('aborted');
}

/**
 * Alias for WPCF7_ContactForm::pref.
 *
 * Differs from WPCF7_ContactForm::pref in that it will always return a string.
 *
 * @return string
 */
function __cf7_additional_setting($name = '', $contact_form = null){
	$contact_form = __cf7_contact_form($contact_form);
	if(is_null($contact_form)){
		return '';
	}
	return (string) $contact_form->pref($name);
}

/**
 * Alias for WPCF7_ContactForm::additional_setting(@param $max = false).
 *
 * Differs from WPCF7_ContactForm::additional_setting in that it will always return an array.
 *
 * @return array
 */
function __cf7_additional_settings($name = '', $contact_form = null){
	$contact_form = __cf7_contact_form($contact_form);
	if(is_null($contact_form)){
		return [];
	}
	return $contact_form->additional_setting($name, false);
}

/**
 * Alias for wpcf7_contact_form, wpcf7_get_contact_form_by_hash and wpcf7_get_contact_form_by_title.
 *
 * Returns the current contact form if the specified setting has a falsey value and restores the current contact form.
 *
 * @return null|WPCF7_ContactForm
 */
function __cf7_contact_form($contact_form = null){
	$current_contact_form = wpcf7_get_current_contact_form();
	if(empty($contact_form)){ // 0, false, null and other PHP falsey values.
		return $current_contact_form;
	}
	if(__cf7_is_contact_form($contact_form)){
		return $contact_form;
	}
	$post_id = __cf7_hash_exists($contact_form); // Hash-based contact form identification.
	if($post_id){
		$contact_form = wpcf7_contact_form($post_id); // Avoid wpcf7_get_contact_form_by_hash for backcompat.
	} elseif(is_numeric($contact_form)){
		$contact_form = __cf7_contact_form_by('id', $contact_form);
	} elseif(is_string($contact_form)){
        $contact_form = __cf7_contact_form_by('title', $contact_form);
    } elseif($contact_form instanceof \WP_Post){
		$contact_form = wpcf7_contact_form($contact_form);
	} else {
		return null;
	}
	if(__cf7_is_contact_form($current_contact_form)){
		wpcf7_contact_form($current_contact_form); // Restores the current contact form.
	}
	return $contact_form;
}

/**
 * @return null|WPCF7_ContactForm
 */
function __cf7_contact_form_by($field = '', $value = null){
    if('hash' === $field and version_compare(WPCF7_VERSION, '5.8', '<')){ // https://contactform7.com/2023/08/06/contact-form-7-58/#hash-based-contact-form-identification
		return null;
	}
	if('id' === $field){
		$value = __absint($value);
		if(!$value){
			return null;
		}
	}
	if(in_array($field, ['hash', 'title'])){
		if(!is_string($value)){
			return null;
		}
		$value = (trim) $value;
		if(!$value){
			return null;
		}
	}
	switch($field){
		case 'hash':
			$contact_form = wpcf7_get_contact_form_by_hash($value);
			break;
		case 'id':
			$contact_form = wpcf7_contact_form($value);
			break;
		case 'title':
			$contact_form = wpcf7_get_contact_form_by_title($value);
			break;
		default:
			return null;
	}
	return $contact_form;
}

/**
 * Skips or sends emails based on user input values and contact form email templates avoiding mail_sent and mail_failed action hooks.
 *
 * This function MUST be called inside the 'wpcf7_before_send_mail' action hook.
 *
 * @return bool
 */
function __cf7_fake_mail($contact_form = null, $submission = null){
	if(!doing_action('wpcf7_before_send_mail')){
        return; // Too early or too late.
    }
	$contact_form = __cf7_contact_form($contact_form);
	if(is_null($contact_form)){
		return false;
	}
	$submission = __cf7_submission($submission);
	if(is_null($submission)){
		return false;
	}
	if(!$submission->is('init')){
		return false; // Avoid conflicts with other statuses.
	}
	if(__cf7_skip_mail($contact_form) or __cf7_mail($contact_form)){
		$message = $contact_form->message('mail_sent_ok');
		$message = __cf7_message($message, 'mail_sent');
		$submission->set_response($message);
		$submission->set_status('mail_sent');
		return true;
	}
	$message = $contact_form->message('mail_sent_ng');
	$message = __cf7_message($message);
	$submission->set_response($message);
	$submission->set_status('mail_failed');
	return false;
}

/**
 * @return bool
 */
function __cf7_has_additional_setting($name = '', $contact_form = null){
	$contact_form = __cf7_contact_form($contact_form);
	if(is_null($contact_form)){
		return false;
	}
	$pref = $contact_form->pref($name);
	return !is_null($pref);
}

/**
 * @return bool
 */
function __cf7_has_posted_data($key = ''){
	if(empty($key)){
		return false;
	}
	$data = __cf7_posted_raw();
	return isset($data[$key]);
}

/**
 * Alias for wpcf7_get_contact_form_by_hash.
 *
 * Differs from wpcf7_get_contact_form_by_hash in that it will always return an integer.
 *
 * @return int
 */
function __cf7_hash_exists($hash = ''){
	global $wpdb;
	if(!is_string($hash)){
        return 0;
    }
	$hash = trim($hash);
	if(strlen($hash) < 7){
		return 0;
	}
	$like = $wpdb->esc_like($hash) . '%';
	$query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_hash' AND meta_value LIKE %s";
	$query = $wpdb->prepare($query, $like);
	$post_id = $wpdb->get_var($query);
	return (int) $post_id;
}

/**
 * @return array
 */
function __cf7_invalid_fields($fields = [], $contact_form = null){
	$contact_form = __cf7_contact_form($contact_form);
	if(is_null($contact_form)){
		return [];
	}
	if(!__is_associative_array($fields)){
		return [];
	}
	$invalid = [];
	$tags = wp_list_pluck($contact_form->scan_form_tags('feature=name-attr'), 'type', 'name');
	foreach($fields as $name => $types){
        if(!isset($tags[$name])){
            continue;
        }
        if(!in_array($tags[$name], (array) $types)){
            $invalid[] = $name;
        }
	}
	return $invalid;
}

/**
 * @return bool
 */
function __cf7_is_contact_form($contact_form = null){
	return $contact_form instanceof \WPCF7_ContactForm;
}

/**
 * Opposite of WPCF7_ContactForm::is_true.
 *
 * @return bool
 */
function __cf7_is_false($name = '', $contact_form = null){
	$pref = __cf7_additional_setting($name, $contact_form);
    return __is_false($pref);
}

/**
 * @return bool
 */
function __cf7_is_submission($submission = null){
	return $submission instanceof \WPCF7_Submission;
}

/**
 * Alias for WPCF7_ContactForm::is_true.
 *
 * @return bool
 */
function __cf7_is_true($name = '', $contact_form = null){
	$pref = __cf7_additional_setting($name, $contact_form);
    return __is_true($pref);
}

/**
 * @return WPCF7_ContactForm|WP_Error
 */
function __cf7_localize($contact_form = null, $overwrite_messages = false){
	$contact_form = __cf7_contact_form($contact_form);
	if(is_null($contact_form)){
		$message = translate('The requested contact form was not found.', 'contact-form-7');
		return __error($message, [
			'status' => 404,
		]);
	}
	$contact_form_id = $contact_form->id();
	$locale = get_locale();
	if($locale === get_post_meta($contact_form_id, '_locale', true) and !$overwrite_messages){
		return $contact_form;
	}
	$args = [
		'id' => $contact_form_id,
		'locale' => $locale,
	];
	if($overwrite_messages){
        $messages = wpcf7_messages();
		$args['messages'] = wp_list_pluck($messages, 'default');
	}
	$contact_form = wpcf7_save_contact_form($args);
	if(!$contact_form){
		return __error(translate('There was an error saving the contact form.', 'contact-form-7'), [
			'status' => 500,
		]);
	}
	return $contact_form;
}

/**
 * Alias for WPCF7_Submission::mail.
 *
 * @return bool
 */
function __cf7_mail($contact_form = null){
	$contact_form = __cf7_contact_form($contact_form);
	if(is_null($contact_form)){
		return false;
	}
	$skip_mail = __cf7_skip_mail($contact_form);
	if($skip_mail){
		return true;
	}
	$result = \WPCF7_Mail::send($contact_form->prop('mail'), 'mail');
	if(!$result){
		return false;
	}
	$additional_mail = [];
	if($mail_2 = $contact_form->prop('mail_2') and $mail_2['active']){
		$additional_mail['mail_2'] = $mail_2;
	}
	$additional_mail = apply_filters('wpcf7_additional_mail', $additional_mail, $contact_form);
	foreach($additional_mail as $name => $template){
		\WPCF7_Mail::send($template, $name);
	}
	return true;
}

/**
 * Alias for WPCF7_ContactForm::filter_message.
 *
 * @return string
 */
function __cf7_message($message = '', $status = ''){
	$message = wpcf7_mail_replace_tags($message);
	$message = apply_filters('wpcf7_display_message', $message, $status);
	$message = wp_strip_all_tags($message);
	return $message;
}

/**
 * @return array
 */
function __cf7_metadata($contact_form = null, $submission = null){
	$contact_form = __cf7_contact_form($contact_form);
	if(is_null($contact_form)){
		return [];
	}
	$submission = __cf7_submission($submission);
	if(is_null($submission)){
		return [];
	}
	$metadata = [
        'contact_form_id' => $contact_form->id(),
        'contact_form_locale' => $contact_form->locale(),
        'contact_form_name' => $contact_form->name(),
        'contact_form_title' => $contact_form->title(),
        'container_post_id' => $submission->get_meta('container_post_id'),
        'current_user_id' => $submission->get_meta('current_user_id'),
        'remote_ip' => $submission->get_meta('remote_ip'),
        'remote_port' => $submission->get_meta('remote_port'),
        'submission_response' => $submission->get_response(),
        'submission_status' => $submission->get_status(),
        'timestamp' => $submission->get_meta('timestamp'),
        'unit_tag' => $submission->get_meta('unit_tag'),
        'url' => $submission->get_meta('url'),
        'user_agent' => $submission->get_meta('user_agent'),
    ];
	return $metadata;
}

/**
 * @return array
 */
function __cf7_missing_fields($fields = [], $contact_form = null){
	$contact_form = __cf7_contact_form($contact_form);
	if(is_null($contact_form)){
		return [];
	}
    if(!is_array($fields)){
		return [];
	}
	if(__is_associative_array($fields)){
		$fields = array_keys($fields);
	}
	$missing = [];
	$tags = wp_list_pluck($contact_form->scan_form_tags('feature=name-attr'), 'type', 'name');
	foreach($fields as $name){
		if(isset($tags[$name])){
			continue;
		}
		$missing[] = $name;
	}
	return $missing;
}

/**
 * @return int
 */
function __cf7_object_number($contact_form = null){
	$contact_form = __cf7_contact_form($contact_form);
	if(is_null($contact_form)){
		return 0;
	}
	$pattern = '/^wpcf7-f(\d+)-p(\d+)-o(\d+)$/';
	$unit_tag = $contact_form->unit_tag();
	if(preg_match_all($pattern, $unit_tag, $matches)){
		$o = (int) $matches[3][0];
	} else {
		$pattern = '/^wpcf7-f(\d+)-o(\d+)$/';
		if(preg_match_all($pattern, $unit_tag, $matches)){
			$o = (int) $matches[2][0];
		} else {
			$o = 0;
		}
	}
	return $o;
}

/**
 * @return array
 */
function __cf7_posted_array($key = ''){
	if(empty($key)){
		return [];
	}
	$data = (array) __cf7_posted_data($key);
	$data = wpcf7_array_flatten($data);
	return $data;
}

/**
 * Alias for WPCF7_Submission::get_posted_data.
 *
 * Differs from WPCF7_Submission::get_posted_data in that it will avoid filters.
 *
 * @return array|null|string
 */
function __cf7_posted_data($key = ''){
	$data = __cf7_posted_raw();
	if(empty($key)){
		return $data;
	}
	if(!__cf7_has_posted_data($key)){
		return null;
	}
	return $data[$key];
}

/**
 * Alias for WPCF7_Submission::get_posted_data(@param $name = '').
 *
 * Differs from WPCF7_Submission::get_posted_data in that it will avoid filters.
 *
 * @return array
 */
function __cf7_posted_raw(){
    $data = (array) __get_cache('cf7_posted_data', []);
    if(empty($data)){
        $data = array_filter((array) $_POST, function($key){
            return '_' !== substr($key, 0, 1);
        }, ARRAY_FILTER_USE_KEY);
        $data = __cf7_sanitize_posted_data($data);
        __set_cache('cf7_posted_data', $data);
    }
	return $data;
}

/**
 * Alias for WPCF7_Submission::get_posted_string.
 *
 * Differs from WPCF7_Submission::get_posted_string in that it will avoid filters.
 *
 * @return string
 */
function __cf7_posted_string($key = ''){
	$data = __cf7_posted_array($key);
	if(empty($data)){
        return '';
    }
	return trim($data[0]); // Returns the first array item.
}

/**
 * Alias for WPCF7_Submission::sanitize_posted_data.
 *
 * @return array|string
 */
function __cf7_sanitize_posted_data($value = []){
    if(is_array($value)){
        $value = array_map('__cf7_sanitize_posted_data', $value);
    } elseif(is_string($value)){
        $value = wp_check_invalid_utf8($value);
        $value = wp_kses_no_null($value);
    }
	return $value;
}

/**
 * Alias for WPCF7_ContactForm::shortcode_attr.
 *
 * Differs from WPCF7_ContactForm::shortcode_attr in that it will always return a string.
 *
 * @return string
 */
function __cf7_shortcode_attr($name = '', $contact_form = null){
	$contact_form = __cf7_contact_form($contact_form);
	if(is_null($contact_form)){
		return '';
	}
	return (string) $contact_form->shortcode_attr($name);
}

/**
 * @return bool
 */
function __cf7_skip_mail($contact_form = null){
	$contact_form = __cf7_contact_form($contact_form);
	if(is_null($contact_form)){
		return false;
	}
	$skip_mail = ($contact_form->in_demo_mode() or $contact_form->is_true('skip_mail') or !empty($contact_form->skip_mail));
	$skip_mail = apply_filters('wpcf7_skip_mail', $skip_mail, $contact_form);
	return (bool) $skip_mail;
}

/**
 * Alias for WPCF7_Submission::get_instance.
 *
 * Returns the current submission if the specified setting has a falsey value.
 *
 * @return null|WPCF7_Submission
 */
function __cf7_submission($submission = null){
	$current_submission = \WPCF7_Submission::get_instance();
	if(empty($submission)){ // 0, false, null and other PHP falsey values.
		return $current_submission;
	}
	if(__cf7_is_submission($submission)){
		return $submission;
	}
	return null;
}
