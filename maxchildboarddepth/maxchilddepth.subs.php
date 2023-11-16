<?php

function maxchilddepth_settings(&$config_vars)
{
	global $txt;

	loadLanguage('maxchilddepth');

	$config_vars = array_merge($config_vars, array(
		array('int', 'boardindex_max_depth', 'subtext' => $txt['boardindex_max_depth_desc']),
		'',
	));
}