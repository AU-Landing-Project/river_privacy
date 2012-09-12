<?php

/**
 * 	River Privacy
 * 	Makes non-object oriented river entries private
 * 	eg. friendships
 */

function river_privacy_init(){
	if(elgg_get_plugin_setting('hide_old_items', 'river_privacy') != 'no'){
		elgg_set_config('river_privacy_legacy', TRUE);
	}	
	
	// set the river item to private if it's not an object
	elgg_register_plugin_hook_handler('creating', 'river', 'river_privacy_creating_river');
}


//
// hook called before river creation
// return associative array of parameters to create the river entry
function river_privacy_creating_river($hook, $type, $returnvalue, $params){
	if($returnvalue['type'] != 'object'){
		$returnvalue['access_id'] = ACCESS_PRIVATE;
	}
	
	return $returnvalue;
}

elgg_register_event_handler('init', 'system', 'river_privacy_init');