<?php

/**
 * @return array
 */
function __wf_bulk_countries(){
    if(__isset_cache('wf_bulk_countries')){
        return (array) __get_cache('wf_bulk_countries', []);
    }
    if(!__is_plugin_active('wordfence/wordfence.php')){
        __set_cache('wf_bulk_countries', []);
        return [];
    }
    require(WORDFENCE_PATH . 'lib/wfBulkCountries.php'); /** @var array $wfBulkCountries */
    asort($wfBulkCountries);
    __set_cache('wf_bulk_countries', $wfBulkCountries);
    return $wfBulkCountries;
}

/**
 * @return array
 */
function __wf_countries($preferred_countries = []){
    $wf_countries = __wf_bulk_countries();
    if(!$preferred_countries){
        return $wf_countries;
    }
    $countries = [];
    foreach($preferred_countries as $iso2){
        if(!isset($wf_countries[$iso2])){
            continue;
        }
        $countries[$iso2] = $wf_countries[$iso2];
        unset($wf_countries[$iso2]);
    }
    return array_merge($countries, $wf_countries);
}
