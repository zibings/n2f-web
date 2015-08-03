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
	 * $Id: constants.inc.php 199 2012-07-10 02:18:52Z amale@EPSILON $
	 */

	// Debug Level Constants
	define('N2F_DEBUG_OFF',							0);
	define('N2F_DEBUG_ERROR',						1);
	define('N2F_DEBUG_WARN',							2);
	define('N2F_DEBUG_NOTICE',						4);

	// File Structure Constants
	define('N2F_FS_CURRENT',							0);
	define('N2F_FS_LEGACY',							1);

	// N2 Framework Yverdon Notification Event Constants
	define('N2F_EVT_EXTENSION_LOADED',					'N2F_EVT_EXTENSION_LOADED');
	define('N2F_EVT_MODULES_LOADED',					'N2F_EVT_MODULES_LOADED');
	define('N2F_EVT_CORE_LOADED',						'N2F_EVT_CORE_LOADED');
	define('N2F_EVT_MODULE_LOADED',					'N2F_EVT_MODULE_LOADED');
	define('N2F_EVT_ERROR_THROWN',					'N2F_EVT_ERROR_THROWN');
	define('N2F_EVT_WARNING_THROWN',					'N2F_EVT_WARNING_THROWN');
	define('N2F_EVT_NOTICE_THROWN',					'N2F_EVT_NOTICE_THROWN');

	// N2 Framework Yverdon System Handler Event Constants
	define('N2F_EVT_DESTRUCT',						'N2F_EVT_DESTRUCT');
	define('N2F_EVT_MODULES_INIT',					'N2F_EVT_MODULES_INIT');
	define('N2F_EVT_CORE_INIT',						'N2F_EVT_CORE_INIT');
	define('N2F_EVT_MODULE_INIT',						'N2F_EVT_MODULE_INIT');

	// N2 Framework Yverdon Error Number List
	define('N2F_ERROR_NO_LANGUAGE_SET',				'0001');
	define('N2F_ERROR_MODULE_FAILURE',					'0002');

	// N2 Framework Yverdon Notice Number List
	define('N2F_NOTICE_EXTENSION_LOADED',				'0001');
	define('N2F_NOTICE_MODULES_LOADED',				'0002');
	define('N2F_NOTICE_CORE_LOADED',					'0004');
	define('N2F_NOTICE_N2FCLASS_LOADED',				'0005');
	define('N2F_NOTICE_MODULE_LOADED',					'0006');
	define('N2F_NOTICE_EVENT_ADDED',					'0007');
	define('N2F_NOTICE_EVENT_TOUCHED',					'0008');
	define('N2F_NOTICE_LANG_KEY_SET',					'0009');

	// N2 Framework Yverdon Warning Number List
	define('N2F_WARN_EXTENSION_LOAD_FAILED',			'0001');
	define('N2F_WARN_LANG_KEY_MISSING',				'0002');
	define('N2F_WARN_LANG_KEY_EXISTS',					'0003');
	define('N2F_WARN_EXISTING_EVENT',					'0004');
	define('N2F_WARN_NONEXISTANT_EVENT',				'0005');

	// N2 Framework Yverdon Error Code List
	define('N2F_ERRCODE_MODULE_FAILURE',				'N2F_ERRCODE_MODULE_FAILURE');

	// Random Constants
	define('OS_WINDOWS',							((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? true : false));

?>