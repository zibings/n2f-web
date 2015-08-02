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

	// Get global instance of n2f_cls
	$n2f = n2f_cls::getInstance();

	// Register extension
	$n2f->registerExtension(
		'%EXT_KEY%',
		'%EXT_NAME%',
		'%EXT_VERSION%',
		'%EXT_AUTHOR%',
		'%EXT_URL%'
	);

?>