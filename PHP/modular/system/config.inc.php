<?php

	/***********************************************\
	 * N2F Yverdon v0                              *
	 * Copyright (c) 2009 Zibings Incorporated     *
	 *                                             *
	 * You should have received a copy of the      *
	 * Microsoft Reciprocal License along with     *
	 * this program.  If not, see:                 *
	 * <http://opensource.org/licenses/ms-rl.html> *
	\***********************************************/

	/*
	 * $Id: config.inc.php 192 2012-01-19 20:01:11Z amale@EPSILON $
	 */

	// Declare global configuration variable
	global $cfg; $cfg = array();


	######################################
	## Basic Framework Configuration    ##
	######################################

	// Site configuration
	$cfg['site']['domain']		= '';				# Domain name for your site (ex: n2framework.com)
	$cfg['site']['title']		= '';				# Default title for your site (ex: The N2 Framework)
	$cfg['site']['timezone']		= 'America/New_York';	# Your server's timezone name (see: http://www.php.net/manual/en/timezones.php)

	// Debug level configuration
	$cfg['dbg']['level']		= N2F_DEBUG_NOTICE;		# Current debug level used by core
	$cfg['dbg']['dump_debug']	= false;				# Whether or not to dump all debug information at the end of each page load

	######################################
	## Basic Framework Configuration    ##
	######################################





	######################################
	## Advanced Framework Configuration ##
	######################################

	$cfg['sys_lang']			= 'en';				# System language
	$cfg['charset']			= 'utf-8';			# System charset
	$cfg['content_type']		= 'text/html';			# System content-type
	$cfg['auto_exts']			= array(				# Extensions to be auto-included
		'n2f_cache',
		'n2f_paginate',
		'n2f_return',
		'n2f_session',
		'n2f_template',
		'n2f_database'
	);
	$cfg['file_struct']			= N2F_FS_CURRENT;		# File structure to follow
	$cfg['crypt_hash']			= 'e-8J(@Tm"/=7{c"r+!LwAjT}>Nx7QcB+Lr}Zt53FvnO_;*<}dWe_}3P/t,-ND")';	# Security hash for encryption
	$cfg['def_mods']['start']	= 'main';				# Default starting module
	$cfg['def_mods']['error']	= 'error';			# Default error module
	$cfg['show_ad']			= true;				# Whether or not to show the N2F advertisement in page source

	######################################
	## Advanced Framework Configuration ##
	######################################

?>