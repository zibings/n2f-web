<?php

	// Our global variable(s)
	global $cfg;

	// Cache configuration
	$cfg['cache']['dir']		= './n2f_cache/';		# Cache File Directory (Must be writeable; Will be created automatically if it doesn't exist)
	$cfg['cache']['prefix']		= '';				# Cache File Prefix
	$cfg['cache']['ext']		= '.cache';			# Cache File Extension
	$cfg['cache']['ttl']		= 3600;				# Default amount of time before cache expires  (in seconds, 3600 = 1 hour)
	$cfg['cache']['gc'] 		= true;				# Clean up old cache files occasionally
	$cfg['cache']['memcached'] 	= false;				# Use MemCache by default
	$cfg['cache']['mc_persist']	= true;				# Use persistent Memcache connections
	$cfg['cache']['mc_compress']	= true;				# Use compression for MemCache (requires that zlib be installed)
	$cfg['cache']['mc_threshold']	= 15000;				# String length required before using compression in MemCache
	$cfg['cache']['mc_savings']	= 0.2;				# Minimum savings required to actually store the value compressed in MemCache (value between 0 an 1,  0.2 = 20%)
	$cfg['cache']['mc_servers']	= array(				# MemCache Servers - format:  'server:port' => 'weight'
		'localhost:11211' => '64'
	);

?>