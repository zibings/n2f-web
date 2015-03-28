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
	 * $Id: dynamic.ext.php 192 2012-01-19 20:01:11Z amale@EPSILON $
	 */

	// Pull/create the global variables needed
	global $_n2f_dyntpl_aliases, $_n2f_dyntpl_galiases, $_n2f_dyntpl_gbindings;

	// Register extension
	n2f_cls::getInstance()->registerExtension(
		'n2f_template/dynamic',
		'n2f_dynamic_template',
		0.2,
		'Matthew Hykes, Andrew Male',
		'http://n2framework.com/'
	);

	// Create error constants
	define('DYNTPL_ERROR_INVALID_MODULE',				'0001');
	define('DYNTPL_ERROR_TEMPLATE_NOT_FOUND', 			'0002');

	// English error strings
	L('en', 'DYNTPL_ERROR_INVALID_MODULE',				"The requested module is invalid: '_%1%_'");
	L('en', 'DYNTPL_ERROR_TEMPLATE_NOT_FOUND',			"The requested template was not found: '_%1%_'");

	// German error strings
	L('de', 'DYNTPL_ERROR_INVALID_MODULE',				"The requested module is invalid: '_%1%_'");
	L('de', 'DYNTPL_ERROR_TEMPLATE_NOT_FOUND',			"The requested template was not found: '_%1%_'");

	// Spanish error strings
	L('es', 'DYNTPL_ERROR_INVALID_MODULE',				"The requested module is invalid: '_%1%_'");
	L('es', 'DYNTPL_ERROR_TEMPLATE_NOT_FOUND',			"The requested template was not found: '_%1%_'");

	// Swedish error strings
	L('se', 'DYNTPL_ERROR_INVALID_MODULE',				"The requested module is invalid: '_%1%_'");
	L('se', 'DYNTPL_ERROR_TEMPLATE_NOT_FOUND',			"The requested template was not found: '_%1%_'");

	class n2f_template_dynamic {
		const flag_render_exempt	= 1;
		const flag_cache_exempt	= 2;
		const flag_alias_exempt	= 4;

		public static function handle_setBase(n2f_template &$tpl, $base) {
			if (empty($base) || strlen($base) < 1) {
				return(false);
			}

			if (!is_dir($base)) {
				return(false);
			}

			$tpl->base = $base;

			return(true);
		}

		public static function handle_setModule(n2f_template &$tpl, $module) {
			if (empty($module) || strlen($module) < 1) {
				return(false);
			}

			if (!is_dir("{$tpl->base}/{$module}")) {
				return(false);
			}

			$tpl->module = $module;

			return(true);
		}

		public static function handle_setSkin(n2f_template &$tpl, $skin) {
			if (empty($skin) || strlen($skin) < 1) {
				return(false);
			}

			if (!is_dir("{$tpl->base}/{$tpl->module}/tpl/{$skin}")) {
				return(false);
			}

			$tpl->skin = $skin;

			return(true);
		}

		public static function handle_setFile(n2f_template &$tpl, $file) {
			if (empty($file) || strlen($file) < 1) {
				return(false);
			}

			if (strtolower(substr($file, -4)) == '.tpl') {
				$file = substr($file, 0, (strlen($file - 4)));
			}

			if (!file_exists("{$tpl->base}/{$tpl->module}/tpl/{$tpl->skin}/{$file}.tpl")) {
				if ($tpl->skin !== $GLOBALS['cfg']['tpl']['skin'] && !file_exists("{$tpl->base}/{$tpl->module}/tpl/{$tpl->skin}/{$file}.tpl")) {
					return(false);
				}
			}

			$tpl->file = $file;

			return(true);
		}

		public static function handle_setField(n2f_template &$tpl, $fieldname, $fieldvalue) {
			if (empty($fieldname) || strlen($fieldname) < 1) {
				return(false);
			}

			$tpl->fields[$fieldname] = $fieldvalue;

			return(true);
		}

		public static function handle_setFields(n2f_template &$tpl, array $fields) {
			if (count($fields) < 1) {
				return(false);
			}

			foreach ($fields as $fieldname => $fieldvalue) {
				$tpl->fields[$fieldname] = $fieldvalue;
			}

			return(true);
		}

		public static function handle_setExpire(n2f_template &$tpl, $expire) {
			if (empty($expire) || $expire < 0) {
				return(false);
			}

			$tpl->expire = $expire;

			return(true);
		}

		public static function handle_setBinding(n2f_template &$tpl, $pattern, $callback) {
			if ((empty($pattern) || strlen($pattern) < 1) || !is_callable($callback)) {
				return(false);
			}

			if (!isset($tpl->bindings[$pattern])) {
				$tpl->bindings[$pattern] = array($callback);
			} else {
				$tpl->bindings[$pattern][] = $callback;
			}

			return(true);
		}

		public static function handle_setBindings(n2f_template &$tpl, array $bindings) {
			if (count($bindings) < 1) {
				return(false);
			}

			foreach ($bindings as $pattern => $callback) {
				if (!is_callable($callback)) {
					continue;
				}

				if (!isset($tpl->bindings[$pattern])) {
					$tpl->bindings[$pattern] = array($callback);
				} else {
					$tpl->bindings[$pattern][] = $callback;
				}
			}

			return(true);
		}

		public static function handle_render(n2f_template $tpl) {
			global $_n2f_dyntpl_aliases, $_n2f_dyntpl_galiases, $_n2f_dyntpl_gbindings, $cfg;
			$n2f = n2f_cls::getInstance();

			$res = true;
			$errors = array();
			$flags = $tpl->getData('flags');

			if ($flags === null) {
				$flags = 0;
			}

			if (!isset($tpl->module)) {
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(DYNTPL_ERROR_INVALID_MODULE, S('DYNTPL_ERROR_INVALID_MODULE'), 'n2f_template/dynamic.ext.php');
				}

				$errors[] = "No module was set";
				$tpl->addData('errors', $errors);
			} else {
				$path = "{$tpl->base}/{$tpl->module}/tpl/{$tpl->skin}/{$tpl->file}.tpl";
				$preChecked = false;

				if (!is_file($path)) {
					if ($cfg['tpl']['skin'] !== $tpl->skin) {
						$path = "{$tpl->base}/{$tpl->module}/tpl/{$cfg['tpl']['skin']}/{$tpl->file}.tpl";
					}
				} else {
					$preChecked = true;
				}

				if (!$preChecked && !is_file($path)) {
					if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
						$n2f->debug->throwError(DYNTPL_ERROR_TEMPLATE_NOT_FOUND, S('DYNTPL_ERROR_TEMPLATE_NOT_FOUND', array($path)), 'n2f_template/dynamic.ext.php');
					}

					$errors[] = "The requested template was not found: {$path}";
					$tpl->addData('errors', $errors);
					$res = false;
				} else {
					if($n2f->hasExtension('n2f_cache') && !($flags & n2f_template_dynamic::flag_cache_exempt)) {
						$cache = new n2f_cache($tpl->expire, 'dyntpl');

						if ($cache->isCached($path)) {
							$tpl->addData('rendered_data', $cache->startCaching($path, null, "", null, true));
						} else {
							$data = file_get_contents($path);
						}
					} else {
						$data = file_get_contents($path);
					}

					if (isset($data) && strlen($data) > 0) {
						$xmlTem = '#<\?xml(.*)\?>#is';
						$matches = array();

						if (preg_match_all($xmlTem, $data, $matches)) {
							for ($i = 0; $i < count($matches[0]); $i++) {
								$content = $matches[0][$i];
								$content = str_replace("'", "\"", $content);
								$content = str_replace(array("<?xml", "?>"), array("<?php echo '<'.'?xml", "?'.'>'; ?>\n"), $content);
								$data = preg_replace($xmlTem, $content, $data, 1);
							}
						}

						$isAlias = $tpl->getData('isAlias');

						if (!($flags & n2f_template_dynamic::flag_alias_exempt) && is_array($_n2f_dyntpl_aliases) && count($_n2f_dyntpl_aliases) > 0) {
							foreach (array_values($_n2f_dyntpl_aliases) as $alias) {
								if ($isAlias == $alias['text']) {
									continue;
								}

								if (strpos($data, '<%'.$alias['text'].'%>') === false) {
									continue;
								}

								$al_path = "{$alias['base']}/{$alias['module']}/tpl/{$alias['skin']}/{$alias['file']}.tpl";

								if (is_file($al_path)) {
									$tmp = new n2f_template('dynamic');
									$tmp->setBase($alias['base'])->setModule($alias['module'])->setSkin($alias['skin'])->setFile($alias['file']);
									n2f_template_dynamic::isAlias($tmp, $alias['text']);
									$tmp->render();

									$contents = $tmp->fetch();
									$al_key = '<%'.$alias['text'].'%>';
									$data = str_replace($al_key, $contents, $data);
								}
							}
						}

						if (!($flags & n2f_template_dynamic::flag_alias_exempt) && is_array($_n2f_dyntpl_galiases) && count($_n2f_dyntpl_galiases) > 0) {
							foreach (array_values($_n2f_dyntpl_galiases) as $galias) {
								if ($isAlias == $galias['text']) {
									continue;
								}

								if (strpos($data, '<%'.$galias['text'].'%>') === false) {
									continue;
								}

								$al_path = "{$tpl->base}/{$galias['module']}/tpl/{$tpl->skin}/{$galias['file']}.tpl";

								if (is_file($al_path)) {
									$tmp = new n2f_template('dynamic');
									$tmp->setBase($tpl->base)->setModule($galias['module'])->setSkin($tpl->skin)->setFile($galias['file']);
									n2f_template_dynamic::isAlias($tmp, $galias['text']);
									$tmp->render();

									$contents = $tmp->fetch();
									$al_key = '<%'.$galias['text'].'%>';
									$data = str_replace($al_key, $contents, $data);
								}
							}
						}

						$constantsPattern = '#<\%([0-9A-Za-z_]{1,})\%>#is';
						$matches = array();

						if (preg_match_all($constantsPattern, $data, $matches)) {
							for ($i = 0; $i < count($matches[0]); $i++) {
								if (defined($matches[1][$i])) {
									$content = $matches[0][$i];
									$content = str_replace(array("<%", "%>"), array("<?php echo ", ";?>"), $content);
									$data = preg_replace($constantsPattern, $content, $data, 1);
								}
							}
						}

						if (!($flags & n2f_template_dynamic::flag_render_exempt)) {
							$data = str_replace(array('<%$', '<%', '%>'), array('<?php echo $', '<?php ', '?>'), $data);
						}

						if (count($tpl->bindings) > 0) {
							foreach ($tpl->bindings as $pattern => $callbacks) {
								if (count($callbacks) > 0) {
									$matches = array();

									if (preg_match_all($pattern, $data, $matches)) {
										for ($i = 0; $i < count($matches[0]); $i++) {
											$originalContent = $matches[0][$i];
											$currentContent = $matches[0][$i];

											foreach (array_values($callbacks) as $callback) {
												$currentContent = call_user_func_array($callback, array($tpl, $currentContent, $originalContent));
											}

											$data = str_replace($originalContent, $currentContent, $data);
										}
									}
								}
							}
						}

						if (is_array($_n2f_dyntpl_gbindings) && count($_n2f_dyntpl_gbindings) > 0) {
							foreach ($_n2f_dyntpl_gbindings as $pattern => $callbacks) {
								if (count($callbacks) > 0) {
									$matches = array();

									if (preg_match_all($pattern, $data, $matches)) {
										for ($i = 0; $i < count($matches[0]); $i++) {
											$originalContent = $matches[0][$i];
											$currentContent = $matches[0][$i];

											foreach (array_values($callbacks) as $callback) {
												$currentContent = call_user_func_array($callback, array($tpl, $currentContent, $originalContent));
											}

											$data = preg_replace($pattern, $currentContent, $data, 1);
										}
									}
								}
							}
						}

						if (isset($cache)) {
							$cache->startCaching($path);

							echo($data);

							$cache->endCaching(false);
						}

						$tpl->addData('rendered_data', $data);
					}
				}
			}

			return($res);
		}

		public static function handle_fetch(n2f_template $tpl) {
			$data = $tpl->getData('rendered_data');
			$flags = $tpl->getData('flags');

			if ($flags == null) {
				$flags = 0;
			}

			if (!($flags & n2f_template_dynamic::flag_render_exempt)) {
				extract($tpl->fields);

				ob_start();

				echo(eval('?>'.$data));

				$output = ob_get_contents();
				ob_end_clean();

				return($output);
			} else {
				return($data);
			}
		}

		public static function handle_display(n2f_template $tpl) {
			echo($tpl->fetch());

			return(true);
		}

		/**
		 * Add skin-specific alias to the global stack.
		 *
		 * @param string $text	Textual name of alias.
		 * @param string $file	Name of template file for alias.
		 * @param string $module	Name of alias template file module.
		 * @param string $base	Base path to look for module/template file.
		 * @param string $skin	Name of skin to look for template within.
		 * @return boolean		Boolean value based on the success of adding the alias.
		 */
		public static function addAlias($text, $file, $module, $base = null, $skin = null) {
			global $_n2f_dyntpl_aliases;

			if ($base === null) {
				$base = './modules';
			}

			if ($skin === null) {
				$skin = $GLOBALS['cfg']['tpl']['skin'];
			}

			if (strtolower(substr($file, -4)) == '.tpl') {
				$file = substr($file, 0, (strlen($file - 4)));
			}

			$path = "{$base}/{$module}/tpl/{$skin}/{$file}.tpl";

			if (!is_file($path)) {
				return(false);
			}

			if ($_n2f_dyntpl_aliases !== null && is_array($_n2f_dyntpl_aliases)) {
				$_n2f_dyntpl_aliases[$text] = array(
					'text'	=> $text,
					'base'	=> $base,
					'module'	=> $module,
					'skin'	=> $skin,
					'file'	=> $file
				);
			} else {
				$_n2f_dyntpl_aliases = array(
					$text => array(
						'text'	=> $text,
						'base'	=> $base,
						'module'	=> $module,
						'skin'	=> $skin,
						'file'	=> $file
					)
				);
			}

			return(true);
		}

		/**
		 * Add a skin-independant alias to the global stack.
		 *
		 * @param string $text	Textual name of global alias.
		 * @param string $file	Name of template file for global alias.
		 * @param string $module	Name of alias template file module.
		 * @return null
		 */
		public static function addGlobalAlias($text, $file, $module) {
			global $_n2f_dyntpl_galiases;

			if ($_n2f_dyntpl_galiases !== null && is_array($_n2f_dyntpl_galiases)) {
				$_n2f_dyntpl_galiases[$text] = array(
					'text'	=> $text,
					'module'	=> $module,
					'file'	=> $file
				);
			} else {
				$_n2f_dyntpl_galiases = array(
					$text => array(
						'text'	=> $text,
						'module'	=> $module,
						'file'	=> $file
					)
				);
			}

			return(null);
		}

		public static function setFlags(n2f_template &$tpl, $flags = null) {
			$tplflags = $tpl->getData('flags');

			if ($tplflags !== null) {
				if ($flags === null) {
					$tplflags = null;
				} else {
					$tplflags = $tplflags | $flags;
				}
			} else {
				if ($flags !== null) {
					$tplflags = $flags;
				}
			}

			$tpl->addData('flags', $tplflags);

			return(null);
		}

		public static function isAlias(n2f_template &$tpl, $al_text) {
			global $_n2f_dyntpl_aliases, $_n2f_dyntpl_galiases;

			if (!isset($_n2f_dyntpl_aliases[$al_text]) && !isset($_n2f_dyntpl_galiases[$al_text])) {
				return(false);
			}

			$tpl->addData('isAlias', $al_text);
			n2f_template_dynamic::setFlags($tpl, (n2f_template_dynamic::flag_render_exempt | n2f_template_dynamic::flag_cache_exempt));

			return(true);
		}

		/**
		 * Add a binding to the global stack.
		 *
		 * @param string $pattern	Regular expression pattern to use for recognizing this binding in a template.
		 * @param callback $callback	Callback to use when the binding has been found in a template.
		 */
		public static function addGlobalBinding($pattern, $callback) {
			global $_n2f_dyntpl_gbindings;

			if (!is_callable($callback)) {
				return;
			}

			if ($_n2f_dyntpl_gbindings !== null && is_array($_n2f_dyntpl_gbindings)) {
				if (isset($_n2f_dyntpl_gbindings[$pattern])) {
					$_n2f_dyntpl_gbindings[$pattern][] = $callback;
				} else {
					$_n2f_dyntpl_gbindings[$pattern] = array($callback);
				}
			} else {
				$_n2f_dyntpl_gbindings = array($pattern => array($callback));
			}

			return;
		}
	}

	// Register the extension with the template system
	n2f_template::addExtension('dynamic',
		array(
			N2F_TPLEVT_SET_BASE		=> array('n2f_template_dynamic', 'handle_setBase'),
			N2F_TPLEVT_SET_MODULE	=> array('n2f_template_dynamic', 'handle_setModule'),
			N2F_TPLEVT_SET_SKIN		=> array('n2f_template_dynamic', 'handle_setSkin'),
			N2F_TPLEVT_SET_FILE		=> array('n2f_template_dynamic', 'handle_setFile'),
			N2F_TPLEVT_SET_FIELD	=> array('n2f_template_dynamic', 'handle_setField'),
			N2F_TPLEVT_SET_FIELDS	=> array('n2f_template_dynamic', 'handle_setFields'),
			N2F_TPLEVT_SET_EXPIRE	=> array('n2f_template_dynamic', 'handle_setExpire'),
			N2F_TPLEVT_SET_BINDING	=> array('n2f_template_dynamic', 'handle_setBinding'),
			N2F_TPLEVT_SET_BINDINGS	=> array('n2f_template_dynamic', 'handle_setBindings'),
			N2F_TPLEVT_RENDER		=> array('n2f_template_dynamic', 'handle_render'),
			N2F_TPLEVT_FETCH		=> array('n2f_template_dynamic', 'handle_fetch'),
			N2F_TPLEVT_DISPLAY		=> array('n2f_template_dynamic', 'handle_display'),
			N2F_TPLEVT_SET_ALIAS	=> array('n2f_template_dynamic', 'addAlias'),
			N2F_TPLEVT_SET_GALIAS	=> array('n2f_template_dynamic', 'addGlobalAlias')
		)
	);

?>