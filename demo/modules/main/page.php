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
	 * $Id: page.php 107 2010-07-30 16:46:35Z amale $
	 */

	// Create our template
	$tpl = new n2f_template('dynamic');
	$tpl->setModule('main')->setFile('index');

	// Add our debug information
	$tpl->setField('debug_information', n2f_cls::getInstance()->dumpDebug(true));

	// Add our registered extensions
	$tpl->setField('registered_exts', n2f_cls::getInstance()->getRegisteredExtensions());

	// Render the template, then echo it the 'fast' way below
	$tpl->render();

?>
<?php echo $tpl->fetch();?>