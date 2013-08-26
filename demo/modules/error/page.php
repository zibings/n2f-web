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
	 * $Id: page.php 113 2010-10-16 04:25:14Z amale $
	 */

	// Create our template
	$tpl = new n2f_template('dynamic');
	$tpl->setModule('error')->setFile('index');

	// Look for our error codes
	switch ($_REQUEST['error_code']) {
		case N2F_ERRCODE_MODULE_FAILURE:
			$error_message = S('N2F_ERRCODE_MODULE_FAILURE', array($_REQUEST['nmod']));
			break;
		default:
			$error_message = "We're not sure what happened, but it must have been bad.  Maybe you should try again?";
			break;
	}

	// Add our error message to the fields
	$tpl->setField('error_message', $error_message);

	// Render our template, then echo it fast-like
	$tpl->render();

?>
<?php echo $tpl->fetch();?>