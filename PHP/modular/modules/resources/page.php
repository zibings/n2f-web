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

	// Create/configure the template
	$tpl = new n2f_template('dynamic');
	$tpl->setModule('resources')->setFile('index');

	// Render and then display the template
	$tpl->render();

?>
<?php echo $tpl->fetch();?>