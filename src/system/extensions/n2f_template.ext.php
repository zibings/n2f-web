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
	 * $Id: n2f_template.ext.php 190 2012-01-14 19:06:21Z amale@EPSILON $
	 */

	// Our global variable(s)
	global $_n2f_tpl_extensions, $cfg;

	// Get global instance of n2f_cls
	$n2f = n2f_cls::getInstance();

	// Register extension
	$n2f->registerExtension(
		'n2f_template',
		'n2f_template',
		0.2,
		'Matthew Hykes, Andrew Male',
		'http://n2framework.com/'
	);

	// Pull in library extensions for configuration/etc
	$n2f->requireExtensions(array('n2f_template/config', 'n2f_template/constants', 'n2f_template/lang'));

	// Check if we need to include our template object
	if (is_array($cfg['tpl']['exts']) && count($cfg['tpl']['exts']) > 0) {
		foreach (array_values($cfg['tpl']['exts']) as $ext) {
			$n2f->loadExtension("n2f_template/{$ext}");
		}
	}

	/**
	 * Core template class.
	 *
	 */
	class n2f_template extends n2f_events {
		/**
		 * Name of the skin directory to look in for the template file.
		 *
		 * @var string
		 */
		public $skin;
		/**
		 * Name of the template file (note: file extension is up to the template engine).
		 *
		 * @var string
		 */
		public $file;
		/**
		 * Base path to look in for modules.
		 *
		 * @var string
		 */
		public $base;
		/**
		 * Name of the module to look in for the template.
		 *
		 * @var string
		 */
		public $module;
		/**
		 * Value for the expire header (useful to prevent unwanted caching in the browser).
		 *
		 * @var string
		 */
		public $expire;
		/**
		 * Fields which will be replaced (filled in with data) when the template file is rendered.
		 *
		 * @var array
		 */
		public $fields;
		/**
		 * Collection of bindings that will be run over template during rendering.
		 *
		 * @var array
		 */
		public $bindings;
		/**
		 * Name of the extension (engine) currently in use by this n2f_template object.
		 *
		 * @var string
		 */
		public $extension;
		/**
		 * Internal container for object data.
		 *
		 * @var array
		 */
		protected $_internalData;
		/**
		 * List of global elements.
		 *
		 * @var array
		 */
		protected static $globals;
		/**
		 * List of callbacks used by the core system for engine calls.
		 *
		 * @var array
		 */
		private static $callbacks = array(
			N2F_TPLEVT_SET_BASE,
			N2F_TPLEVT_SET_MODULE,
			N2F_TPLEVT_SET_SKIN,
			N2F_TPLEVT_SET_FILE,
			N2F_TPLEVT_SET_FIELD,
			N2F_TPLEVT_SET_FIELDS,
			N2F_TPLEVT_SET_EXPIRE,
			N2F_TPLEVT_SET_BINDING,
			N2F_TPLEVT_SET_BINDINGS,
			N2F_TPLEVT_RENDER,
			N2F_TPLEVT_FETCH,
			N2F_TPLEVT_DISPLAY,
			N2F_TPLEVT_SET_ALIAS,
			N2F_TPLEVT_SET_GALIAS
		);


		/**
		 * Static method for adding template extensions to the available list.
		 *
		 * @param string $name		Name of the extension being added.
		 * @param array $callbacks	Array of callbacks revealed by the extension.
		 * @return null
		 */
		public static function addExtension($name, array $callbacks) {
			if (empty($name)) {
				return(null);
			}

			if (count($callbacks) != count(self::$callbacks)) {
				return(null);
			}

			foreach (array_values(self::$callbacks) as $callbk) {
				if (!isset($callbacks[$callbk])) {
					return(null);
				}
			}

			global $_n2f_tpl_extensions;

			$_n2f_tpl_extensions[$name] = $callbacks;

			return(null);
		}

		/**
		 * Static method for adding a global field to the system.
		 *
		 * @param string $fieldname	Name of the field being set.
		 * @param mixed $fieldvalue	Value of the field being set.
		 * @return boolean			Boolean value based on success of set.
		 */
		public static function setGlobalField($fieldname, $fieldvalue) {
			$n2f = n2f_cls::getInstance();

			if (empty($fieldname)) {
				return(false);
			}

			if (self::$globals !== null && is_array(self::$globals)) {
				if (isset(self::$globals['fields']) && is_array(self::$globals['fields'])) {
					self::$globals['fields'][$fieldname] = $fieldvalue;
				} else {
					self::$globals['fields'] = array($fieldname => $fieldvalue);
				}
			} else {
				self::$globals = array(
					'fields'		=> array($fieldname => $fieldvalue),
					'aliases'		=> array(),
					'galiases'	=> array(),
					'bindings'	=> array()
				);
			}

			if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
				$n2f->debug->throwNotice(N2F_NOTICE_TPL_GFIELD_SET, S('N2F_NOTICE_TPL_GFIELD_SET', array($fieldname)), 'n2f_template.ext.php');
			}

			return(true);
		}

		/**
		 * Static method for adding a set of global fields to the system.
		 *
		 * @param array $fields	Array of fields being set.
		 */
		public static function setGlobalFields(array $fields) {
			if (count($fields) > 0) {
				foreach (array_values($fields) as $field) {
					if (is_array($field) && count($field) == 2) {
						self::setGlobalField($field[0], $field[1]);
					}
				}
			}

			return;
		}

		/**
		 * Static method for adding an alias to the system.
		 *
		 * @param string $text	Textual name of alias.
		 * @param string $file	Name of template file for alias.
		 * @param string $module	Name of alias template file module.
		 * @param string $base	Base path to look for module/template file.
		 * @param string $skin	Name of skin to look for template within.
		 */
		public static function setAlias($text, $file, $module, $base = null, $skin = null) {
			$n2f = n2f_cls::getInstance();

			if (self::$globals !== null && is_array(self::$globals)) {
				if (isset(self::$globals['aliases']) && is_array(self::$globals['aliases'])) {
					self::$globals['aliases'][] = array($text, $file, $module, $base, $skin);
				} else {
					self::$globals['aliases'] = array(array($text, $file, $module, $base, $skin));
				}
			} else {
				self::$globals = array(
					'fields'		=> array(),
					'aliases'		=> array(array($text, $file, $module, $base, $skin)),
					'galiases'	=> array(),
					'bindings'	=> array()
				);
			}

			if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
				$n2f->debug->throwNotice(N2F_TPLEVT_ALIAS_SET, S('N2F_TPLEVT_ALIAS_SET', array($text)), 'n2f_template.ext.php');
			}

			return;
		}

		/**
		 * Static method for adding aliases to the system.
		 *
		 * @param array $aliases	Array of aliases being set.
		 */
		public static function setAliases(array $aliases) {
			if (count($aliases) > 0) {
				foreach (array_values($aliases) as $alias) {
					if (is_array($alias) && count($alias) == 3) {
						self::setGlobalAlias($alias[0], $alias[1], $alias[2]);
					}
				}
			}

			return;
		}

		/**
		 * Static method for adding a global alias to the system.
		 *
		 * @param string $text	Textual name of alias.
		 * @param string $file	Name of template file for alias.
		 * @param string $module	Name of alias template file module.
		 */
		public static function setGlobalAlias($text, $file, $module) {
			$n2f = n2f_cls::getInstance();

			if (self::$globals !== null && is_array(self::$globals)) {
				if (isset(self::$globals['galiases']) && is_array(self::$globals['galiases'])) {
					self::$globals['galiases'][] = array($text, $file, $module);
				} else {
					self::$globals['galiases'] = array(array($text, $file, $module));
				}
			} else {
				self::$globals = array(
					'fields'		=> array(),
					'aliases'		=> array(),
					'galiases'	=> array(array($text, $file, $module)),
					'bindings'	=> array()
				);
			}

			if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
				$n2f->debug->throwNotice(N2F_NOTICE_TPL_GALIAS_SET, S('N2F_NOTICE_TPL_GALIAS_SET', array($text)), 'n2f_template.ext.php');
			}

			return;
		}

		/**
		 * Static method for adding global aliases to the system.
		 *
		 * @param array $aliases	Array of aliases being set.
		 */
		public static function setGlobalAliases(array $aliases) {
			if (count($aliases) > 0) {
				foreach (array_values($aliases) as $alias) {
					if (is_array($alias) && count($alias) == 5) {
						self::setGlobalAlias($alias[0], $alias[1], $alias[2], $alias[3], $alias[4]);
					}
				}
			}

			return;
		}

		/**
		 * Static method for adding a global binding to the system.
		 *
		 * @param string $pattern	Regular expression pattern to use for recognizing this binding in a template.
		 * @param callback $callback	Callback to use when the binding has been found in a template.
		 */
		public static function setGlobalBinding($pattern, $callback) {
			$n2f = n2f_cls::getInstance();

			if (self::$globals !== null && is_array(self::$globals)) {
				if (isset(self::$globals['bindings']) && is_array(self::$globals['bindings'])) {
					if (isset(self::$globals['bindings'][$pattern]) && is_array(self::$globals['bindings'][$pattern])) {
						self::$globals['bindings'][$pattern][] = $callback;
					} else {
						self::$globals['bindings'][$pattern] = array($callback);
					}
				} else {
					self::$globals['bindings'] = array($pattern => array($callback));
				}
			} else {
				self::$globals = array(
					'fields'		=> array(),
					'aliases'		=> array(),
					'galiases'	=> array(),
					'bindings'	=> array($pattern => array($callback))
				);
			}

			if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
				$n2f->debug->throwNotice(N2F_NOTICE_TPL_GBINDING_SET, S('N2F_NOTICE_TPL_GBINDING_SET', array($pattern)), 'n2f_template.ext.php');
			}

			return;
		}

		/**
		 * Static method for adding global bindings to the system.
		 *
		 * @param array $bindings	Array of bindings being set.
		 */
		public static function setGlobalBindings(array $bindings) {
			if (count($bindings) > 0) {
				foreach (array_values($bindings) as $binding) {
					if (is_array($binding) && count($binding) == 2) {
						self::setGlobalBinding($binding[0], $binding[1]);
					}
				}
			}

			return;
		}

		/**
		 * Static method for adding a global tag to the system.
		 *
		 * @param string $tagname	Name of tag (used for regular expression).
		 * @param callback $callback	Callback to use when the binding has been found in a template.
		 */
		public static function setGlobalTag($tagname, $callback) {
			self::setGlobalBinding("#<{$tagname}(.*?)/>#is", $callback);

			return;
		}

		/**
		 * Static method for adding global tags to the system.
		 *
		 * @param array $tags	Array of tags being set.
		 */
		public static function setGlobalTags(array $tags) {
			if (count($tags) > 0) {
				foreach (array_values($tags) as $tag) {
					if (is_array($tag) && count($tag) == 2) {
						self::setGlobalTag($tag[0], $tag[1]);
					}
				}
			}

			return;
		}

		/**
		 * Static method for adding a global block to the system.
		 *
		 * @param string $tagname	Name of block tag (used for regular expression).
		 * @param callback $callback	Callback to use when the binding has been found in a template.
		 */
		public static function setGlobalBlock($tagname, $callback) {
			self::setGlobalBinding("#<{$tagname}(.*?)>(.*?)</{$tagname}>#is", $callback);

			return;
		}

		/**
		 * Static method for adding global blocks to the system.
		 *
		 * @param array $blocks	Array of blocks being set.
		 */
		public static function setGlobalBlocks(array $blocks) {
			if (count($blocks) > 0) {
				foreach (array_values($blocks) as $block) {
					if (is_array($block) && count($block) == 2) {
						self::setGlobalBlock($block[0], $block[1]);
					}
				}
			}

			return;
		}

		/**
		 * Retrieves any attributes for the given markup tag from the provided content.
		 *
		 * @param string $tagName	Name of tag (used for regular expression).
		 * @param string $tagContent	Content to search for tag.
		 * @return array			Array of attributes and their values if found.
		 */
		public static function getTagAttributes($tagname, $tagcontent) {
			$pattern = "#<{$tagname}(.*?)/>#is";
			$matches = array();
			$ret = array();

			$tagcontent = str_replace(array('<?php', '?>', '<%', '%>'), array('_%php%', '%php%_', '_%tpl%', '%tpl%_'), $tagcontent);

			if (preg_match_all($pattern, $tagcontent, $matches)) {
				if (isset($matches[1]) && !empty($matches[1][0])) {
					$attribsString = trim($matches[1][0]);

					if (strlen($attribsString) > 0) {
						$attribsString = str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $attribsString);

						try {
							$attribXml = new SimpleXMLElement("<element $attribsString />", LIBXML_NOERROR | LIBXML_NOWARNING);

							if (count($attribXml->attributes()) > 0) {
								foreach ($attribXml->attributes() as $key => $val) {
									$val = str_replace(array('&amp;', '&lt;', '&gt;', '_%php%', '%php%_', '_%tpl%', '%tpl%_'), array('&', '<', '>', '<?php', '?>', '<%', '%>'), $val);
									$ret[$key] = (string)$val;
								}
							}
						} catch (Exception $e) {
							if (n2f_cls::getInstance()->debug->showLevel(N2F_DEBUG_ERROR)) {
								n2f_cls::getInstance()->debug->throwError(N2F_ERROR_TPL_SIMPLEXML_PARSE, S('N2F_ERROR_TPL_SIMPLEXML_PARSE', array($e->getMessage())), 'n2f_template.ext.php');
							}
						}
					}
				}
			}

			return($ret);
		}

		/**
		 * Retrieves any attributes for the given markup block from the provided content.
		 *
		 * @param string $tagName	Name of block tag (used for regular expression).
		 * @param string $tagContent	Content to search for block tag.
		 * @return array			Array of attributes and their values if found.
		 */
		public static function getBlockAttributes($tagname, $blockcontent) {
			$pattern = "#<{$tagname}(.*?)>(.*?)</{$tagname}>#is";
			$matches = array();
			$ret = array();

			$blockcontent = str_replace(array('<?php', '?>', '<%', '%>'), array('_%php%', '%php%_', '_%tpl%', '%tpl%_'), $blockcontent);

			if (preg_match_all($pattern, $blockcontent, $matches)) {
				if (isset($matches[1]) && !empty($matches[1][0])) {
					$attribsString = trim($matches[1][0]);

					if (strlen($attribsString) > 0) {
						$attribsString = str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $attribsString);

						try {
							$attribXml = new SimpleXMLElement("<element $attribsString />", LIBXML_NOERROR | LIBXML_NOWARNING);

							if (count($attribXml->attributes()) > 0) {
								foreach ($attribXml->attributes() as $key => $val) {
									$val = str_replace(array('&amp;', '&lt;', '&gt;', '_%php%', '%php%_', '_%tpl%', '%tpl%_'), array('&', '<', '>', '<?php', '?>', '<%', '%>'), $val);
									$ret[$key] = (string)$val;
								}
							}
						} catch (Exception $e) {
							if (n2f_cls::getInstance()->debug->showLevel(N2F_DEBUG_ERROR)) {
								n2f_cls::getInstance()->debug->throwError(N2F_ERROR_TPL_SIMPLEXML_PARSE, S('N2F_ERROR_TPL_SIMPLEXML_PARSE', array($e->getMessage())), 'n2f_template.ext.php');
							}
						}
					}
				}
			}

			return($ret);
		}

		/**
		 * Attempts to find markup tags within the provided content.
		 *
		 * @param string $tagName		Name of tag (used for regular expression).
		 * @param string $currentContent	Content to search for tag.
		 * @return array				Array of matching markup tags including attributes, empty array if none found.
		 */
		public static function getInnerTag($tagname, $currentcontent) {
			$pattern = "#<{$tagname}(.*?)/>#is";
			$matches = array();
			$ret = array();

			if (preg_match_all($pattern, $currentcontent, $matches)) {
				$ret = array(
					'matched'		=> $matches[0][0],
					'attributes'	=> self::getTagAttributes($tagname, $matches[0][0])
				);
			}

			return($ret);
		}

		/**
		 * Attempts to find markup blocks within the provided content.
		 *
		 * @param string $tagName		Name of block tag (used for regular expression).
		 * @param string $currentContent	Content to search for block tag.
		 * @return array				Array of matching markup blocks including attributes, empty array if none found.
		 */
		public static function getInnerBlock($tagname, $currentcontent) {
			$pattern = "#<{$tagname}(.*?)>(.*?)</{$tagname}>#is";
			$matches = array();
			$ret = array();

			if (preg_match_all($pattern, $currentcontent, $matches)) {
				$ret = array(
					'matched'		=> $matches[0][0],
					'inner'		=> $matches[2][0],
					'attributes'	=> self::getBlockAttributes($tagname, $matches[0][0])
				);
			}

			return($ret);
		}


		/**
		 * Initializes a new n2f_template object.
		 *
		 * @return n2f_template
		 */
		public function __construct() {
			parent::__construct();

			$num_args = func_num_args();

			if ($num_args > 0) {
				$n2f = null;
				$ext = null;
				$cfg = null;

				for ($i = 0; $i < $num_args; ++$i) {
					$arg = func_get_arg($i);

					if ($arg instanceof n2f_cls) {
						$n2f = $arg;

						continue;
					}

					if ($arg instanceof n2f_template_config) {
						$cfg = $arg;

						continue;
					}

					if (is_string($arg) && strlen($arg) > 0) {
						$ext = $arg;

						continue;
					}
				}
			} else {
				$n2f = n2f_cls::getInstance();
				$ext = '';
				$cfg = null;
			}

			if ($n2f === null) {
				$n2f = n2f_cls::getInstance();
			}

			$this->base = './modules';
			$this->module = $n2f->cfg->def_mods->start;
			$this->skin = $GLOBALS['cfg']['tpl']['skin'];
			$this->file = 'index';
			$this->fields = array();
			$this->bindings = array();
			$this->expire = $GLOBALS['cfg']['tpl']['exp'];

			global $_n2f_tpl_extensions;

			if (isset($cfg) && $cfg !== null) {
				$this->base = (isset($cfg->base)) ? $cfg->base : $this->base;
				$this->module = (isset($cfg->module)) ? $cfg->module : $this->module;
				$this->skin = (isset($cfg->skin)) ? $cfg->skin : $this->skin;
				$this->file = (isset($cfg->file)) ? $cfg->file : $this->file;
				$this->fields = (isset($cfg->fields)) ? $cfg->fields : $this->fields;
				$this->expire = (isset($cfg->expire)) ? $cfg->expire : $this->expire;

				$this->addData('config', $cfg);
			}

			$this->addEvent(N2F_TPLEVT_BASE_SET);
			$this->addEvent(N2F_TPLEVT_MODULE_SET);
			$this->addEvent(N2F_TPLEVT_SKIN_SET);
			$this->addEvent(N2F_TPLEVT_FILE_SET);
			$this->addEvent(N2F_TPLEVT_FIELD_SET);
			$this->addEvent(N2F_TPLEVT_FIELDS_SET);
			$this->addEvent(N2F_TPLEVT_EXPIRE_SET);
			$this->addEvent(N2F_TPLEVT_BINDING_SET);
			$this->addEvent(N2F_TPLEVT_BINDINGS_SET);
			$this->addEvent(N2F_TPLEVT_RENDERED);
			$this->addEvent(N2F_TPLEVT_FETCHED);
			$this->addEvent(N2F_TPLEVT_DISPLAYED);
			$this->addEvent(N2F_TPLEVT_SET_BASE, true);
			$this->addEvent(N2F_TPLEVT_SET_MODULE, true);
			$this->addEvent(N2F_TPLEVT_SET_SKIN, true);
			$this->addEvent(N2F_TPLEVT_SET_FILE, true);
			$this->addEvent(N2F_TPLEVT_SET_FIELD, true);
			$this->addEvent(N2F_TPLEVT_SET_FIELDS, true);
			$this->addEvent(N2F_TPLEVT_SET_EXPIRE, true);
			$this->addEvent(N2F_TPLEVT_SET_BINDING, true);
			$this->addEvent(N2F_TPLEVT_SET_BINDINGS, true);
			$this->addEvent(N2F_TPLEVT_RENDER, true);
			$this->addEvent(N2F_TPLEVT_FETCH, true);
			$this->addEvent(N2F_TPLEVT_DISPLAY, true);
			$this->addEvent(N2F_TPLEVT_SET_ALIAS, true);
			$this->addEvent(N2F_TPLEVT_SET_GALIAS, true);

			if (!isset($_n2f_tpl_extensions[$ext])) {
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(N2F_ERROR_TPL_EXTENSION_NOT_LOADED, S('N2F_ERROR_TPL_EXTENSION_NOT_LOADED', array($ext)), 'n2f_template.ext.php');
				}

				$this->extension = null;
			} else {
				$this->extension = $ext;

				foreach (array_values(self::$callbacks) as $callback) {
					$this->hookEvent($callback, $_n2f_tpl_extensions[$ext][$callback]);
				}
			}

			return($this);
		}

		/**
		 * Mechanism for storing data in the n2f_template object's internal data container.
		 *
		 * @param mixed $key	Key for data being stored.
		 * @param mixed $data	Actual data being stored.
		 * @return null
		 */
		public function addData($key, $data) {
			$this->_internalData[$key] = $data;

			return(null);
		}

		/**
		 * Retrieves data from the n2f_template object's internal data container.
		 *
		 * @param mixed $key	Key for the data being retrieved.
		 * @return mixed		Mixed value of stored data.
		 */
		public function getData($key) {
			if (isset($this->_internalData[$key])) {
				return($this->_internalData[$key]);
			}

			return(null);
		}

		/**
		 * Sets the base directory where templates are located.
		 *
		 * @param string $base	The base directory for loading templates.
		 * @return n2f_template	n2f_template object for chaining.
		 */
		public function setBase($base) {
			$n2f = n2f_cls::getInstance();

			$result = $this->hitEvent(N2F_TPLEVT_SET_BASE, array(&$this, $base));

			if ($result === true) {
				$this->hitEvent(N2F_TPLEVT_BASE_SET, array(&$this, $base));

				if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
					$n2f->debug->throwNotice(N2F_NOTICE_TPL_BASE_SET, S('N2F_NOTICE_TPL_BASE_SET', array($this->base)), 'n2f_template.ext.php');
				}
			}

			return($this);
		}

		/**
		 * Set the name of the module the system is looking for templates within.
		 *
		 * @param string $module	The name of the module where the templates reside.
		 * @return n2f_template	n2f_template object for chaining.
		 */
		public function setModule($module) {
			$n2f = n2f_cls::getInstance();

			$result = $this->hitEvent(N2F_TPLEVT_SET_MODULE, array(&$this, $module));

			if ($result === true) {
				$this->hitEvent(N2F_TPLEVT_MODULE_SET, array(&$this, $module));

				if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
					$n2f->debug->throwNotice(N2F_NOTICE_TPL_MODULE_SET, S('N2F_NOTICE_TPL_MODULE_SET', array($this->module)), 'n2f_template.ext.php');
				}
			}

			return($this);
		}

		/**
		 * Set the name of the skin the system should look for templates within.
		 *
		 * @param string $skin	Name of the skin where the templates reside.
		 * @return n2f_template	n2f_template object for chaining.
		 */
		public function setSkin($skin) {
			$n2f = n2f_cls::getInstance();

			$result = $this->hitEvent(N2F_TPLEVT_SET_SKIN, array(&$this, $skin));

			if ($result === true) {
				$this->hitEvent(N2F_TPLEVT_SKIN_SET, array(&$this, $skin));

				if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
					$n2f->debug->throwNotice(N2F_NOTICE_TPL_SKIN_SET, S('N2F_NOTICE_TPL_SKIN_SET', array($this->skin)), 'n2f_template.ext.php');
				}
			}

			return($this);
		}

		/**
		 * Set the filename (without extension) of the template the system will use.
		 *
		 * @param string $file	Name of the template file (without extension).
		 * @return n2f_template	n2f_template object for chaining.
		 */
		public function setFile($file) {
			$n2f = n2f_cls::getInstance();

			$result = $this->hitEvent(N2F_TPLEVT_SET_FILE, array(&$this, $file));

			if ($result == true) {
				$this->hitEvent(N2F_TPLEVT_FILE_SET, array(&$this, $file));

				if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
					$n2f->debug->throwNotice(N2F_NOTICE_TPL_FILE_SET, S('N2F_NOTICE_TPL_FILE_SET', array($this->file)), 'n2f_template.ext.php');
				}
			}

			return($this);
		}

		/**
		 * Set a field in the template.
		 *
		 * @param string $fieldname	Name of the field being set
		 * @param mixed $fieldvalue	Value of the field being set
		 * @return n2f_template		n2f_template object for chaining.
		 */
		public function setField($fieldname, $fieldvalue) {
			$n2f = n2f_cls::getInstance();

			$result = $this->hitEvent(N2F_TPLEVT_SET_FIELD, array(&$this, $fieldname, $fieldvalue));

			if ($result === true) {
				$this->hitEvent(N2F_TPLEVT_FIELD_SET, array(&$this, $fieldname, $fieldvalue));

				if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
					$n2f->debug->throwNotice(N2F_NOTICE_TPL_FIELD_SET, S('N2F_NOTICE_TPL_FIELD_SET', array($fieldname, debugEcho($fieldvalue, true))), 'n2f_template.ext.php');
				}
			}

			return($this);
		}

		/**
		 * Set fields in the template (note: $fields needs to be an associative array of fieldname=>fieldvalue pairs).
		 *
		 * @param array $fields	Array of fields being set in the template.
		 * @return n2f_template	n2f_template object for chaining.
		 */
		public function setFields(array $fields) {
			$n2f = n2f_cls::getInstance();

			$result = $this->hitEvent(N2F_TPLEVT_SET_FIELDS, array(&$this, $fields));

			if ($result === true) {
				$this->hitEvent(N2F_TPLEVT_FIELDS_SET, array(&$this, $fields));

				if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
					$n2f->debug->throwNotice(N2F_NOTICE_TPL_FIELDS_SET, S('N2F_NOTICE_TPL_FIELDS_SET', array(print_r($fields, true))), 'n2f_template.ext.php');
				}
			}

			return($this);
		}

		/**
		 * Set the expiration time (in seconds) for the template.
		 *
		 * @param integer $expire	Time until template cache expires (if applicable).
		 * @return n2f_template		n2f_template object for chaining.
		 */
		public function setExpire($expire) {
			$n2f = n2f_cls::getInstance();

			$result = $this->hitEvent(N2F_TPLEVT_SET_EXPIRE, array(&$this, $expire));

			if ($result === true) {
				$this->hitEvent(N2F_TPLEVT_EXPIRE_SET, array(&$this, $expire));

				if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
					$n2f->debug->throwNotice(N2F_NOTICE_TPL_EXPIRE_SET, S('N2F_NOTICE_TPL_EXPIRE_SET', array($this->expire)), 'n2f_template.ext.php');
				}
			}

			return($this);
		}

		/**
		 * Set a binding in the template.
		 *
		 * @param string $pattern	Regular expression pattern to use for recognizing this binding in a template.
		 * @param callback $callback	Callback to use when the binding has been found in a template.
		 * @return n2f_template		n2f_template object for chaining.
		 */
		public function setBinding($pattern, $callback) {
			$n2f = n2f_cls::getInstance();

			$result = $this->hitEvent(N2F_TPLEVT_SET_BINDING, array(&$this, $pattern, $callback));

			if ($result === true) {
				$this->hitEvent(N2F_TPLEVT_BINDING_SET, array(&$this, $pattern, $callback));

				if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
					$n2f->debug->throwNotice(N2F_NOTICE_TPL_BINDING_SET, S('N2F_NOTICE_TPL_BINDING_SET', array($pattern, callback_toString($callback))), 'n2f_template.ext.php');
				}
			}

			return($this);
		}

		/**
		 * Set bindings in the template (note: $bindings needs to be an associative array of pattern=>callback pairs).
		 *
		 * @param array $bindings	Array of bindings being set in the template.
		 * @return n2f_template		n2f_template object for chaining.
		 */
		public function setBindings(array $bindings) {
			$n2f = n2f_cls::getInstance();

			$result = $this->hitEvent(N2F_TPLEVT_SET_BINDINGS, array(&$this, $bindings));

			if ($result === true) {
				$this->hitEvent(N2F_TPLEVT_BINDING_SET, array(&$this, $bindings));

				if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
					$n2f->debug->throwNotice(N2F_NOTICE_TPL_BINDINGS_SET, S('N2F_NOTICE_TPL_BINDING_SET', array(print_r($bindings, true))), 'n2f_template.ext.php');
				}
			}

			return($this);
		}

		/**
		 * Prepare the template for display.
		 *
		 * @return n2f_template	n2f_template object for chaining.
		 */
		public function render() {
			$n2f = n2f_cls::getInstance();

			if (is_array(self::$globals) && count(self::$globals) == 4) {
				if (isset(self::$globals['fields']) && is_array(self::$globals['fields']) && count(self::$globals['fields']) > 0) {
					$this->setFields(self::$globals['fields']);
				}

				if (isset(self::$globals['aliases']) && is_array(self::$globals['aliases']) && count(self::$globals['aliases']) > 0) {
					foreach (array_values(self::$globals['aliases']) as $alias) {
						$this->hitEvent(N2F_TPLEVT_SET_ALIAS, array($alias[0], $alias[1], $alias[2], $alias[3], $alias[4]));
					}
				}

				if (isset(self::$globals['galiases']) && is_array(self::$globals['galiases']) && count(self::$globals['galiases']) > 0) {
					foreach (array_values(self::$globals['galiases']) as $galias) {
						$this->hitEvent(N2F_TPLEVT_SET_GALIAS, array($galias[0], $galias[1], $galias[2]));
					}
				}

				if (isset(self::$globals['bindings']) && is_array(self::$globals['bindings']) && count(self::$globals['bindings']) > 0) {
					foreach (self::$globals['bindings'] as $pattern => $callbacks) {
						if (is_array($callbacks) && count($callbacks) > 0) {
							foreach (array_values($callbacks) as $callback) {
								$this->setBinding($pattern, $callback);
							}
						}
					}
				}
			}

			$this->hitEvent(N2F_TPLEVT_RENDER, array(&$this));
			$this->hitEvent(N2F_TPLEVT_RENDERED, array(&$this));

			if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
				$n2f->debug->throwNotice(N2F_NOTICE_TPL_RENDER, S('N2F_NOTICE_TPL_RENDER'), 'n2f_template.ext.php');
			}

			return($this);
		}

		/**
		 * Fetch the rendered data from the template.
		 *
		 * @return string	String of rendered template data.
		 */
		public function fetch() {
			$n2f = n2f_cls::getInstance();

			$result = $this->hitEvent(N2F_TPLEVT_FETCH, array(&$this));
			$this->hitEvent(N2F_TPLEVT_FETCHED, array(&$this));

			if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
				$n2f->debug->throwNotice(N2F_NOTICE_TPL_FETCH, S('N2F_NOTICE_TPL_FETCH'), 'n2f_template.ext.php');
			}

			return($result);
		}

		/**
		 * Display the rendered data from the template.
		 *
		 */
		public function display() {
			$n2f = n2f_cls::getInstance();

			$result = $this->hitEvent(N2F_TPLEVT_DISPLAY, array(&$this));

			if ($result === true) {
				$this->hitEvent(N2F_TPLEVT_DISPLAYED, array(&$this));

				if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
					$n2f->debug->throwNotice(N2F_NOTICE_TPL_DISPLAY, S('N2F_NOTICE_TPL_DISPLAY'), 'n2f_template.ext.php');
				}
			}
		}
	}

	/**
	 * Core template configuration class.
	 *
	 */
	class n2f_template_config {
		/**
		 * Name of the skin directory to look in for the template file.
		 *
		 * @var string
		 */
		public $skin;
		/**
		 * Name of the template file (note: file extension is up to the template engine).
		 *
		 * @var string
		 */
		public $file;
		/**
		 * Base path to look in for modules.
		 *
		 * @var string
		 */
		public $base;
		/**
		 * Name of the module to look in for the template.
		 *
		 * @var string
		 */
		public $module;
		/**
		 * Value for the expire header (useful to prevent unwanted caching in the browser).
		 *
		 * @var integer
		 */
		public $expire;

		/**
		 * Initializes a new n2f_template_config object.
		 *
		 * @param string $base			Base directory for template object.
		 * @param string $module			Module directory for template object.
		 * @param string $skin			Skin directory for template object.
		 * @param string $file			File name (without extension) for the template object.
		 * @param integer $expire		Time in seconds for template cache to expire (if applicable).
		 * @return n2f_template_config	n2f_template_config object, can't be used for chaining because of some strangeness in PHP < 5.3?  I think...
		 */
		public function __construct($base = null, $module = null, $skin = null, $file = null, $expire = null) {
			$this->base = $base;
			$this->module = $module;
			$this->skin = $skin;
			$this->file = $file;
			$this->expire = $expire;

			return(null);
		}
	}

	/**
	 * Configuration class for the template engine and extensions.
	 *
	 */
	class n2f_cfg_tpl {
		/**
		 * The current template engine skin.
		 *
		 * @var string
		 */
		public $skin;
		/**
		 * The current template engine expiration time.  (Only works if the Cache extension is installed)
		 *
		 * @var integer
		 */
		public $exp;

		/**
		 * Initializes a new n2f_cfg_tpl object.
		 *
		 * @param array $vals
		 * @return n2f_cfg_tpl
		 */
		public function __construct(array $vals = null) {
			if ($vals === null) {
				$this->skin = 'default';
				$this->exp = 1500;
			} else {
				$this->skin = (isset($vals['skin'])) ? $vals['skin'] : 'default';
				$this->exp = (isset($vals['exp'])) ? $vals['exp'] : 1500;
			}

			return($this);
		}
	}

?>