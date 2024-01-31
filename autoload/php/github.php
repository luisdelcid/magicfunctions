<?php

/**
 * @return array|WP_Error
 */
function __github_latest_release($url = ''){
    $url = trailingslashit($url) . 'releases/latest';
    $response = __remote_get($url);
    return $response;
}

/**
 * @return array|WP_Error
 */
function __github_download_latest($url = '', $args = []){
    $release = __github_latest_release($url);
    if(is_wp_error($release)){
        return $release;
    }
    $zipball_url = isset($release['zipball_url']) ? $release['zipball_url'] : '';
    $file = __remote_download($zipball_url, $args);
    return [
        'file' => $file,
        'release' => $release,
    ];
}

/**
 * @return string|WP_Error
 */
function __github_download_main($url = '', $args = []){
    $url = trailingslashit($url) . 'archive/refs/heads/main.zip';
    $file = __remote_download($url, $args);
    return $file;
}
