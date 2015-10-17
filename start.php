<?php

namespace AU\RiverPrivacy;

const PLUGIN_ID = 'river_privacy';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');

/**
 *	Plugin Init
 */
function init() {
	// set the river item to private if it's not an object
	elgg_register_plugin_hook_handler('creating', 'river', __NAMESPACE__ . '\\creating_river_hook');
}


 
/**
 * hook called before river creation
 * return associative array of parameters to create the river entry
 * 
 * @param type $hook
 * @param type $type
 * @param string $returnvalue
 * @param type $params
 * @return string
 */
function creating_river_hook($hook, $type, $returnvalue, $params) {

	if ($returnvalue['type'] != 'object') {
		$returnvalue['access_id'] = ACCESS_PRIVATE;
	}

	return $returnvalue;
}
