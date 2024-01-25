<?php

    /**
     * @return bool
     */
    function are_plugins_active($plugins = []){
    	if(!is_array($plugins)){
    		return false;
    	}
    	foreach($plugins as $plugin){
    		if(!$this->is_plugin_active($plugin)){
    			return false;
    		}
    	}
    	return true;
    }

    /**
     * @return bool
     */
    function is_plugin_active($plugin = ''){
    	if(!function_exists('is_plugin_active')){
    		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    	}
    	return is_plugin_active($plugin);
    }

    /**
     * @return bool
     */
    function is_plugin_deactivating($file = ''){
    	global $pagenow;
    	if(!@is_file($file)){
    		return false;
    	}
    	return (is_admin() and 'plugins.php' === $pagenow and isset($_GET['action'], $_GET['plugin']) and 'deactivate' === $_GET['action'] and plugin_basename($file) === $_GET['plugin']);
    }

    /**
     * @return string
     */
    function plugin_basename($file = ''){
    	if(!$file){
    		$file = $this->caller_file();
    	}
    	$plugin_file = $this->plugin_file($file);
    	if(!$plugin_file){
    		return '';
    	}
    	return plugin_basename($plugin_file);
    }

    /**
     * @return array|WP_Error
     */
    function plugin_data($file = '', $markup = true, $translate = true){
        if(!$file){
    		$file = $this->caller_file();
    	}
        $plugin_file = $this->plugin_file($file);
    	if(!$plugin_file){
    		return $this->error(__('Plugin not found.'));
    	}
        $md5 = md5($plugin_file);
        $key = 'plugin_data_' . $md5;
        if($this->isset_cache($key)){
            return (array) $this->get_cache($key, []);
        }
        if(!function_exists('get_plugin_data')){
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $data = get_plugin_data($plugin_file, $markup, $translate);
        $this->set_cache($key, $data);
        return $data;
    }

    /**
     * @return string
     */
    function plugin_enqueue($filename = '', $deps = [], $in_footer_l10n_media = true){
    	$file = $this->caller_file();
    	$plugin_file = $this->plugin_file($file);
    	if(!$plugin_file){
    		return '';
    	}
    	$mimes = [
    		'css' => 'text/css',
    		'js' => 'application/javascript',
    	];
    	$filename = wp_basename($filename);
    	$filetype = wp_check_filetype($filename, $mimes);
    	if(!$filetype['type']){
    		return '';
    	}
        $file = plugin_dir_path($plugin_file) . 'src/' . $filetype['ext'] . '/' . $filename; // Relative to the directory path for the plugin __FILE__.
    	if(!file_exists($file)){
    		return '';
    	}
    	$handle = wp_basename($filename, '.' . $filetype['ext']);
    	$handle = $this->plugin_slug($handle, $plugin_file);
    	$is_script = false;
    	if('application/javascript' === $filetype['type']){
    		$deps[] = $this->slug('singleton');
    		$in_footer_media = true;
    		$is_script = true;
    		$l10n = [];
    		if($this->is_associative_array($in_footer_l10n_media)){
    			$l10n = $in_footer_l10n_media;
    		} else {
                $in_footer_media = (bool) $in_footer_l10n_media;
            }
    	} else { // text/css
    		$in_footer_media = 'all';
    		if(is_string($in_footer_l10n_media)){
    			$in_footer_media = $in_footer_l10n_media;
    		}
    	}
    	$this->local_enqueue($handle, $file, $deps, $in_footer_media);
        if(!$is_script){
            return $handle;
        }
        if(!$l10n){
            return $handle;
        }
        $object_name = $this->canonicalize($handle);
        wp_localize_script($handle, $object_name . '_l10n', $l10n);
    	return $handle;
    }

    /**
     * @return string
     */
    function plugin_file($file = ''){
    	global $wp_plugin_paths;
    	if(!$file){
    		$file = $this->caller_file();
    	}
    	if(!file_exists($file)){
    		return '';
    	}
        $md5 = md5($file);
        $key = 'plugin_file_' . $md5;
        if($this->isset_cache($key)){
            return (string) $this->get_cache($key, '');
        }
    	// $wp_plugin_paths contains normalized paths.
    	$file = wp_normalize_path($file);
    	arsort($wp_plugin_paths);
    	foreach($wp_plugin_paths as $dir => $realdir){
    		if(0 !== strpos($file, $realdir)){
    			continue;
    		}
    		$file = $dir . substr($file, strlen($realdir));
    	}
    	$plugin_dir = wp_normalize_path(WP_PLUGIN_DIR);
    	$mu_plugin_dir = wp_normalize_path(WPMU_PLUGIN_DIR);
    	if(!preg_match('#^' . preg_quote($plugin_dir, '#') . '/|^' . preg_quote($mu_plugin_dir, '#') . '/#', $file)){
            $this->set_cache($key, '');
    		return ''; // File is not a plugin.
    	}
    	if(preg_match('#^' . preg_quote($plugin_dir, '#') . '/#', $file)){
    		$dir = $plugin_dir; // File is a plugin.
    	} else {
    		$dir = $mu_plugin_dir; // File is a must-use plugin.
    	}
    	// Get relative path from plugins directory.
    	$file = preg_replace('#^' . preg_quote($plugin_dir, '#') . '/|^' . preg_quote($mu_plugin_dir, '#') . '/#', '', $file);
    	$file = trim($file, '/');
    	if(strpos($file, '/') === false){
    		$part = $file; // The entire plugin consists of just a single PHP file, like Hello Dolly.
    	} else {
    		$parts = explode('/', $file, 2);
    		$part = trailingslashit($parts[0]);
    	}
    	$active_plugins = (array) get_option('active_plugins', []);
    	foreach($active_plugins as $active_plugin){
            if(0 !== strpos($active_plugin, $part)){
                continue;
            }
            $file = $dir . '/' . $active_plugin;
            $this->set_cache($key, $file);
            return $file; // File is a plugin.
    	}
    	$active_sitewide_plugins = (array) get_site_option('active_sitewide_plugins', []);
    	$active_sitewide_plugins = array_keys($active_sitewide_plugins);
    	foreach($active_sitewide_plugins as $active_sitewide_plugin){
            if(0 !== strpos($active_sitewide_plugin, $part)){
                continue;
            }
            $file = $dir . '/' . $active_sitewide_plugin;
            $this->set_cache($key, $file);
            return $file; // File is a must-use plugin.
    	}
        $this->set_cache($key, '');
    	return '';
    }

    /**
     * @return string
     */
    function plugin_folder($file = ''){
    	if(!$file){
    		$file = $this->caller_file();
    	}
    	$basename = $this->plugin_basename($file);
    	if(false === strpos($basename, '/')){
    		return ''; // Ignore. The entire plugin consists of just a single PHP file, like Hello Dolly.
    	}
    	$parts = explode('/', $basename, 2);
    	return $parts[0];
    }

    /**
     * @return string
     */
    function plugin_meta($key = '', $file = ''){
        if(!$file){
    		$file = $this->caller_file();
    	}
        $data = $this->plugin_data($file);
        if(is_wp_error($data)){
            return $data->get_error_message();
        }
    	if(array_key_exists($key, $data)){
    		$arr = $data;
    	} elseif(array_key_exists($key, $data['sections'])){
    		$arr = $data['sections'];
    	} else {
    		return $key . ' ' . __('(not found)');
    	}
    	return $arr[$key];
    }

    /**
     * @return string
     */
    function plugin_prefix($str = '', $file = ''){
    	if(!$file){
    		$file = $this->caller_file();
    	}
    	$plugin_folder = $this->plugin_folder($file);
    	if(!$plugin_folder){
    		return '';
    	}
    	return $this->str_prefix($str, $plugin_folder);
    }

    /**
     * @return string
     */
    function plugin_slug($str = '', $file = ''){
    	if(!$file){
    		$file = $this->caller_file();
    	}
    	$plugin_folder = $this->plugin_folder($file);
    	if(!$plugin_folder){
    		return '';
    	}
    	return $this->str_slug($str, $plugin_folder);
    }

    /**
     * @return void
     */
    function plugin_update_check($url = '', $file = ''){
    	$url = wp_http_validate_url($url);
    	if(!$url){
    		return;
    	}
    	if(!$file){
    		$file = $this->caller_file();
    	}
    	$plugin_file = $this->plugin_file($file);
    	if(!$plugin_file){
    		return '';
    	}
    	$slug = $this->plugin_slug(false, $file);
    	$metadata_url = add_query_arg([
    		'action' => 'get_metadata',
    		'slug' => $slug,
    	], $url);
    	$this->check_for_updates($metadata_url, $plugin_file, $slug);
    }

    /**
     * @return void
     */
    function plugin_update_license($license = '', $file = ''){
    	if(!$license){
    		return;
    	}
    	if(!$file){
    		$file = $this->caller_file();
    	}
    	$slug = $this->plugin_slug(false, $file);
    	$this->set_update_license($slug, $license);
    }
