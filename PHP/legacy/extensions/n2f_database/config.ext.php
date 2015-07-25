<?php

	// Our global variable(s)
	global $_n2f_db_extensions, $cfg;

	// Database configuration
	$cfg['db']['type']			= '';				# Database extension type to use (ex: mysqli, pgsql, etc)
	$cfg['db']['host']			= '';				# Hostname of database server
	$cfg['db']['name']			= '';				# Name of database to select
	$cfg['db']['user']			= '';				# Username to use when authenticating
	$cfg['db']['pass']			= '';				# Password to use when authenticating
	$cfg['db']['file']			= '';				# Filename to use with file-based engines
	$cfg['db']['exts']			= array(				# Available database extensions
		'mysql',
		'mysqli',
		'pgsql',
		'sqlite'
	);

?>