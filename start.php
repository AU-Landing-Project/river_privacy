<?php

namespace AU\RiverPrivacy;

const PLUGIN_ID = 'river_privacy';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');

/**
 * 	Plugin Init
 */
function init() {
	// set the river item to private if it's not an object
	elgg_register_plugin_hook_handler('creating', 'river', __NAMESPACE__ . '\\creating_river_hook');

	// filter river views if necessary
	elgg_register_plugin_hook_handler('view_vars', 'page/components/list', __NAMESPACE__ . '\\filter_list_vars');
	
	// add access check back into the river queries
	elgg_register_plugin_hook_handler('get_sql', 'access', __NAMESPACE__ . '\\river_access_query');
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

/**
 * filter the items sent to a list view
 * 
 * @param type $hook
 * @param type $type
 * @param type $return
 * @param type $params
 * @return type
 */
function filter_list_vars($hook, $type, $return, $params) {
	$filter_river = elgg_get_plugin_setting('hide_old_items', PLUGIN_ID);
	if ($filter_river == 'no') {
		// no need to filter
		return $return;
	}

	if ($return['items'] && is_array($return['items'])) {
		foreach ($return['items'] as $key => $item) {
			if (!($item instanceof \ElggRiverItem)) {
				continue;
			}

			if ($item->type == 'object') {
				continue;
			}

			if ($item->subject_guid == elgg_get_logged_in_user_guid()) {
				continue;
			}

			if (elgg_is_admin_logged_in()) {
				continue;
			}
			
			if (elgg_get_ignore_access()) {
				continue;
			}

			unset($return['items'][$key]);
		}
	}
	
	return $return;
}

/**
 * Add a custom access clause for river queries
 * 
 * @param type $hook
 * @param type $type
 * @param array $return
 * @param type $params
 * @return type
 */
function river_access_query($hook, $type, $return, $params) {

	// anything else we can use to isolate river queries?
	// currently 'oe' is only used in core by river queries
	// but it's not really a great way to judge...
	if ($params['table_alias'] != 'oe') {
		return $return;
	}
	
	if ($params['ignore_access']) {
		return $return;
	}
	
	if (elgg_is_admin_logged_in()) {
		return $return;
	}

	$guid = (int) elgg_get_logged_in_user_guid();
	$return['ands'][] = "((rv.type != 'object' AND rv.subject_guid = {$guid}) OR rv.access_id != 0)";
	
	return $return;
}