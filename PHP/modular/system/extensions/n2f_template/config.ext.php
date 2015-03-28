<?php

	// Our global variable(s)
	global $_n2f_tpl_extensions, $cfg;

	// Template configuration
	$cfg['tpl']['skin']			= 'default';			# Name of skin directory to use by default
	$cfg['tpl']['exp']			= 1;					# Number of seconds before a cache file is considered stale
	$cfg['tpl']['exts']			= array(				# Available template extensions
		'dynamic',
		'static'
	);

?>