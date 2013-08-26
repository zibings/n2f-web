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
	 * $Id: core.inc.php 105 2010-07-10 10:06:40Z amale $
	 */

	// Pull core files into system
	require(N2F_REL_PATH.'system/includes/constants.inc.php');
	require(N2F_REL_PATH.'system/includes/utilities.inc.php');
	require(N2F_REL_PATH.'system/includes/strings.inc.php');
	require(N2F_REL_PATH.'system/classes/events.cls.php');
	require(N2F_REL_PATH.'system/classes/debug.cls.php');
	require(N2F_REL_PATH.'system/classes/n2f.cls.php');
	require(N2F_REL_PATH.'system/config.inc.php');

	// Put path variables into configuration
	$cfg['site']['rel_path'] = N2F_REL_PATH;
	$cfg['site']['url_path'] = N2F_URL_PATH;

	// Set the time zone for the site
	date_default_timezone_set($cfg['site']['timezone']);

	// Create system object
	global $n2f; $n2f = n2f_cls::setInstance($cfg);

	// show that we did our work
	if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
		$n2f->debug->throwNotice(N2F_NOTICE_N2FCLASS_LOADED, S('N2F_NOTICE_N2FCLASS_LOADED'), 'system/classes/n2f.cls.php');
	}

	// Pull in all of our other extensions
	if (is_array($n2f->cfg->auto_exts) && count($n2f->cfg->auto_exts) > 0) {
		foreach (array_values($n2f->cfg->auto_exts) as $ext) {
			$n2f->loadExtension($ext);
		}
	}

	// let the system do it's dirty work
	$n2f->initModules();
	$n2f->initCore();

?>