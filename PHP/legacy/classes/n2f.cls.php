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
	 * $Id: n2f.cls.php 192 2012-01-19 20:01:11Z amale@EPSILON $
	 */

	/**
	 * Core class for N2 Framework Yverdon
	 *
	 */
	class n2f_cls extends n2f_events {
		/**
		 * Holds configuration values.
		 *
		 * @var n2f_cfg
		 */
		public $cfg;
		/**
		 * List of loaded extensions.
		 *
		 * @var array
		 */
		private $extensions;
		/**
		 * List of registered extension meta information.
		 *
		 * @var array
		 */
		private $extensionsMeta;
		/**
		 * Internal debug utility object.
		 *
		 * @var n2f_debug
		 */
		public $debug;
		/**
		 * List of 'global' variables for the framework.
		 *
		 * @var array
		 */
		public $globals;
		/**
		 * Protected static property to hold singleton global.
		 *
		 * @var n2f_cls
		 */
		protected static $_instance = null;


		/**
		 * Method to get the current n2f_cls instance.
		 *
		 * @return n2f_cls	The current n2f_cls global object.
		 */
		public static function &getInstance() {
			// If the instance hasn't been instantiated, do so
			if (n2f_cls::$_instance === null) {
				n2f_cls::$_instance = new n2f_cls();
			}

			// Return variable so we are doubly sure we're in memory
			$instance = n2f_cls::$_instance;

			// Return reference
			return($instance);
		}

		/**
		 * Method to set the current n2f_cls instance.
		 *
		 * @param array $cfg	Array value of optional configuration values.
		 * @return n2f_cls		The current n2f_cls global object.
		 */
		public static function &setInstance(array $cfg = null) {
			// Override instance with configured copy
			n2f_cls::$_instance = new n2f_cls($cfg);
			$instance = n2f_cls::$_instance;

			// Return reference
			return($instance);
		}


		/**
		 * Initializes a new n2f_cls object.
		 *
		 * @param array $cfg	Array value of optional configuration values.
		 */
		public function __construct(array $cfg = null) {
			// Initialize event subsystem and all base properties
			parent::__construct();
			$this->extensions = array();
			$this->extensionsMeta = array();
			$this->cfg = new n2f_cfg($cfg);
			$this->debug = new n2f_debug($this->cfg->dbg);
			$this->globals = array();

			// Add all system and notification events
			$this->addEvent(N2F_EVT_EXTENSION_LOADED);
			$this->addEvent(N2F_EVT_MODULES_LOADED);
			$this->addEvent(N2F_EVT_CORE_LOADED);
			$this->addEvent(N2F_EVT_MODULE_LOADED);
			$this->addEvent(N2F_EVT_DESTRUCT);
			$this->addEvent(N2F_EVT_MODULES_INIT, true);
			$this->addEvent(N2F_EVT_CORE_INIT, true);
			$this->addEvent(N2F_EVT_MODULE_INIT, true);

			// Hook default callbacks for system events
			$this->hookEvent(N2F_EVT_MODULES_INIT, array($this, '_initModules'));
			$this->hookEvent(N2F_EVT_CORE_INIT, array($this, '_initCore'));
			$this->hookEvent(N2F_EVT_MODULE_INIT, array($this, '_initModule'));

			// Register our shutdown function for cleanup/notification
			register_shutdown_function(array($this, '_cleanResources'));
		}

		/**
		 * Creates a new event inside the n2f_cls object.
		 *
		 * @param string $name		String value of event name.
		 * @param boolean $system	Boolean value of event's type.
		 * @return n2f_cls			The n2f_cls object.
		 */
		public function createEvent($name, $system = false) {
			// Make sure it doesn't already exist, we don't want to overwrite our base events
			if (isset($this->events[$name])) {
				// If we're throwing warnings, throw the warning
				if ($this->debug->showLevel(N2F_DEBUG_WARN)) {
					$this->debug->throwWarning(N2F_WARN_EXISTING_EVENT, S('N2F_WARN_EXISTING_EVENT', array($name)), 'system/classes/n2f.cls.php');
				}

				// Return for chaining
				return($this);
			}

			// Add the event
			$this->addEvent($name, $system);

			// If we're throwing notices, throw the notice
			if ($this->debug->showLevel(N2F_DEBUG_NOTICE)) {
				$this->debug->throwNotice(N2F_NOTICE_EVENT_ADDED, S('N2F_NOTICE_EVENT_ADDED', array($name)), 'system/classes/n2f.cls.php');
			}

			// Return for chaining
			return($this);
		}

		/**
		 * Touches an event inside the n2f_cls object.
		 *
		 * @param string $name	String value of the event to touch.
		 * @param array $args	Optional array of arguments to pass to event.
		 * @return n2f_cls		The n2f_cls object.
		 */
		public function touchEvent($name, array $args = null) {
			// Array of core events for security
			$coreEvents = array(
				N2F_EVT_EXTENSION_LOADED	=> false,
				N2F_EVT_MODULES_LOADED	=> false,
				N2F_EVT_CORE_LOADED		=> false,
				N2F_EVT_MODULE_LOADED	=> false,
				N2F_EVT_DESTRUCT		=> false,
				N2F_EVT_MODULES_INIT	=> false,
				N2F_EVT_CORE_INIT		=> false,
				N2F_EVT_MODULE_INIT		=> false
			);

			// If the event doesn't exist or is one of our core ones, don't call it
			if (!isset($this->events[$name]) || isset($coreEvents[$name])) {
				// If we're throwing warnings, throw the warning
				if ($this->debug->showLevel(N2F_DEBUG_WARN)) {
					$this->debug->throwWarning(N2F_WARN_NONEXISTANT_EVENT, S('N2F_WARN_NONEXISTANT_EVENT', array($name)), 'system/classes/n2f.cls.php');
				}

				// Return for chaining
				return($this);
			}

			// Hit the event and let it ride
			$this->hitEvent($name, $args);

			// If we're throwing notices, throw the notice
			if ($this->debug->showLevel(N2F_DEBUG_NOTICE)) {
				$this->debug->throwNotice(N2F_NOTICE_EVENT_ADDED, S('N2F_NOTICE_EVENT_TOUCHED', array($name)), 'system/classes/n2f.cls.php');
			}

			// Return for chaining
			return($this);
		}

		/**
		 * Loads an extension into the system.
		 *
		 * @param string $name	String value of extension's file.
		 * @return n2f_cls		The n2f_cls object.
		 */
		public function loadExtension($name) {
			// If extension is already loaded or $name is blank, just return for chaining
			if (isset($this->extensions[$name]) || empty($name)) {
				return($this);
			}

			// Make sure the extension exists in the filesystem
			if (file_exists("{$this->cfg->site->rel_path}extensions/{$name}.ext.php")) {
				// Add the extension to the quick-list now to avoid recursion
				$this->extensions[$name] = $name;

				// Include the extension file and trigger event notification
				include("{$this->cfg->site->rel_path}extensions/{$name}.ext.php");
				$this->hitEvent(N2F_EVT_EXTENSION_LOADED, array(&$this, $name));

				// If we're throwing notices, throw the notice
				if ($this->debug->showLevel(N2F_DEBUG_NOTICE)) {
					$this->debug->throwNotice(N2F_NOTICE_EXTENSION_LOADED, S('N2F_NOTICE_EXTENSION_LOADED', array($name)), 'system/classes/n2f.cls.php');
				}
			} else { // Extension file isn't in filesystem
				// If we're throwing warnings, throw the warning
				if ($this->debug->showLevel(N2F_DEBUG_WARN)) {
					$this->debug->throwWarning(N2F_WARN_EXTENSION_LOAD_FAILED, S('N2F_WARN_EXTENSION_LOAD_FAILED', array($name)), 'system/classes/n2f.cls.php');
				}
			}

			// Return for chaining
			return($this);
		}

		/**
		 * Returns true or false based on whether or not the given extension has been loaded.
		 *
		 * @param string $name	String value of extension's name.
		 * @return boolean		Boolean value based on extension's status.
		 */
		public function hasExtension($name) {
			// I assure you, we don't load blanks
			if (empty($name)) {
				return(false);
			}

			// If it's in the quick-list, we're golden
			if (isset($this->extensions[$name])) {
				return(true);
			}

			// Not cool bro
			return(false);
		}

		/**
		 * Requires a single extension be included in the system.
		 *
		 * @param string $extension	String value of extension to require.
		 * @return boolean			Boolean value based on successful inclusion of supplied extension.
		 */
		public function requireExtension($extension) {
			// Can't include an empty extension
			if (empty($extension)) {
				return(false);
			}

			// If it's already here, consider it required
			if ($this->hasExtension($extension)) {
				return(true);
			}

			// Try loading the extension
			$this->loadExtension($extension);

			// If we succeeded, then succeed
			if ($this->hasExtension($extension)) {
				return(true);
			}

			// Otherwise, no mas
			return(false);
		}

		/**
		 * Requires a list of extensions be included in the system.
		 *
		 * @param array $extensions	Array of extensions to require.
		 * @return boolean			Boolean value based on successful inclusion of all supplied extensions.
		 */
		public function requireExtensions(array $extensions) {
			// If you don't supply any extensions, just leave me alone
			if (count($extensions) < 1) {
				return(false);
			}

			// Start out assuming everything works out, we're optimists!
			$ret = true;

			// Loop through all supplied extensions
			foreach (array_values($extensions) as $extension) {
				// If we've already got the extension loaded, move on
				if ($this->hasExtension($extension)) {
					continue;
				}

				// Otherwise, try loading the extension
				$this->loadExtension($extension);

				// If we succeeded, we'll continue
				if ($this->hasExtension($extension)) {
					continue;
				}

				// Otherwise, let's let ourselves down
				$ret = false;
			}

			// Return collective result
			return($ret);
		}

		/**
		 * Registers an extension's meta data with the core.
		 *
		 * @param string $key		String value of the extension's key name (used for loading).
		 * @param string $name		String value of the extension's name.
		 * @param string $version	String value of the extension's current version.
		 * @param string $author		String value of the extension's author.
		 * @param string $url		String value of the extension's url.
		 * @return n2f_cls			The n2f_cls object.
		 */
		public function registerExtension($key, $name, $version, $author, $url) {
			// If no name given or meta info already available, return for chaining
			if (empty($name) || isset($this->extensionsMeta[$key])) {
				return($this);
			}

			// Load into meta array
			$this->extensionsMeta[$key] = array(
				'key'	=> $key,
				'name'	=> $name,
				'version'	=> $version,
				'author'	=> $author,
				'url'	=> $url
			);

			// Return for chaining
			return($this);
		}

		/**
		 * Returns an array of the given extension's meta data, if provided.
		 *
		 * @param string $name	String value of the extension's key name (used for loading).
		 * @return array		Array of extension meta data if found.
		 */
		public function getExtensionMeta($name) {
			// If no name given or meta info not available, just return nothing
			if (empty($name) || !isset($this->extensionsMeta[$name])) {
				return(null);
			}

			// Return the meta info
			return($this->extensionsMeta[$name]);
		}

		/**
		 * Returns the list of registered extensions.
		 *
		 * @return array	Array of meta data for registered extensions.
		 */
		public function getRegisteredExtensions() {
			// Just return the entire array
			return($this->extensionsMeta);
		}

		/**
		 * Initializes the extensions located in each module directory by hitting the N2F_EVT_MODULES_INIT system handler event.
		 *
		 * @return n2f_cls	The n2f_cls object.
		 */
		public function initModules() {
			// Hit system event and trigger event notification
			$results = $this->hitEvent(N2F_EVT_MODULES_INIT, array(&$this));
			$this->hitEvent(N2F_EVT_MODULES_LOADED, array(&$this, $results));

			// If we're throwing notices, throw the notice
			if ($this->debug->showLevel(N2F_DEBUG_NOTICE)) {
				$this->debug->throwNotice(N2F_NOTICE_MODULES_LOADED, S('N2F_NOTICE_MODULES_LOADED'), 'system/classes/n2f.cls.php');
			}

			// Return for chaining
			return($this);
		}

		/**
		 * Initializes the core by hitting the N2F_EVT_CORE_INIT system handler event.
		 *
		 * @return n2f_cls	The n2f_cls object.
		 */
		public function initCore() {
			// Hit system event and trigger event notification
			$results = $this->hitEvent(N2F_EVT_CORE_INIT, array(&$this));
			$this->hitEvent(N2F_EVT_CORE_LOADED, array(&$this, $results));

			// If we're throwing notices, throw the notice
			if ($this->debug->showLevel(N2F_DEBUG_NOTICE)) {
				$this->debug->throwNotice(N2F_NOTICE_CORE_LOADED, S('N2F_NOTICE_CORE_LOADED'), 'system/classes/n2f.cls.php');
			}

			// Return for chaining
			return($this);
		}

		/**
		 * Initializes the module by hitting the N2F_EVT_MODULE_INIT system handler event.
		 *
		 * @return n2f_cls	The n2f_cls object.
		 */
		public function initModule() {
			// Hit system event and trigger event notification
			$results = $this->hitEvent(N2F_EVT_MODULE_INIT, array(&$this));
			$this->hitEvent(N2F_EVT_MODULE_LOADED, array(&$this, $results));

			// If we're throwing notices, throw the notice
			if ($this->debug->showLevel(N2F_DEBUG_NOTICE)) {
				$this->debug->throwNotice(N2F_NOTICE_MODULE_LOADED, S('N2F_NOTICE_MODULE_LOADED'), 'system/classes/n2f.cls.php');
			}

			// Return for chaining
			return($this);
		}

		/**
		 * System method for initializing all modules.
		 *
		 * @return null	Null value, no real return.
		 */
		protected function _initModules() {
			// Open current module directory
			$handle = @opendir('modules');

			// If we opened OK
			if ($handle !== false) {
				// Begin reading directory items
				$dir_item = @readdir($handle);

				// Loop through while $dir_item is valid
				while ($dir_item) {
					// Check if this is a module directory
					if (is_dir("modules/{$dir_item}")) {
						// Build our filename based on selected file system
						$filename = ($this->cfg->file_struct == N2F_FS_CURRENT) ? "modules/{$dir_item}/sys.ext.php" : "modules/{$dir_item}/cnf/usr.cnf";

						// If the file exists, call it up for duty
						if (file_exists($filename)) {
							require($filename);
						}
					}

					// Iterate to the next directory item
					$dir_item = @readdir($handle);
				}

				// Cleanliness is close to godliness...or something like that
				@closedir($handle);

				// If we're in a nested level-directory
				if ($this->cfg->site->url_path != '/') {
					// Open the root level-directory's modules directory
					$handle = @opendir(N2F_REL_PATH.'modules');

					// If we opened OK
					if ($handle !== false) {
						// Begin reading directory items
						$dir_item = @readdir($handle);

						// Loop through while $dir_item is valid
						while ($dir_item) {
							// Check if this is a module directory
							if (is_dir(N2F_REL_PATH."modules/{$dir_item}")) {
								// Build our filename based on selected file system
								$filename = ($this->cfg->file_struct == N2F_FS_CURRENT) ? N2F_REL_PATH."modules/{$dir_item}/sys.ext.php" : N2F_REL_PATH."modules/{$dir_item}/cnf/usr.cnf";

								// If the file exists, call it up for duty
								if (file_exists($filename)) {
									require($filename);
								}
							}

							// Iterate to the next directory item
							$dir_item = @readdir($handle);
						}

						// Clean it up
						@closedir($handle);
					}
				}
			}

			// Return just so we've returned
			return(null);
		}

		/**
		 * System method for initializing core features. (This is a placeholder)
		 *
		 * @return null	Null value, no real return.
		 */
		protected function _initCore() {
      // Send our content-type header because we're awesome
			header('Content-Type: '.$this->cfg->content_type.'; charset='.$this->cfg->charset);

			// Return just so we've returned
			return(null);
		}

		/**
		 * System method for initializing/loading the current module.
		 *
		 * @return null	Null value, no real return.
		 */
		protected function _initModule() {
			// If we've not had nmod supplied, setup our default
			if (!isset($_REQUEST['nmod'])) {
				$_REQUEST['nmod'] = $this->cfg->def_mods->start;
			}

			// If we've not had nret supplied, setup with default 'page'
			if (!isset($_REQUEST['nret'])) {
				$_REQUEST['nret'] = 'page';
			}

			// If we're using the N2F filesystem
			if ($this->cfg->file_struct == N2F_FS_CURRENT) {
				// Switch through nret and either get data or page as the loader file
				switch ($_REQUEST['nret']) {
					case 'data':
						$filename = 'data.php';
						break;
					case 'page':
					default:
						$filename = 'page.php';
						break;
				}
			} else { // This is the old filesystem (we only support two for now)
				$filename = 'index.php';
			}

			// If we have a mod.ext.php file in this module, go ahead and call it up for duty too
			if (file_exists("modules/{$_REQUEST['nmod']}/mod.ext.php") !== false) {
				require("modules/{$_REQUEST['nmod']}/mod.ext.php");
			}

			// If our loader file doesn't exist
			if (file_exists("modules/{$_REQUEST['nmod']}/{$filename}") === false) {
				// If we're throwing errors, throw the error
				if ($this->debug->showLevel(N2F_DEBUG_ERROR)) {
					$this->debug->throwError(N2F_ERROR_MODULE_FAILURE, S('N2F_ERROR_MODULE_FAILURE', array($_REQUEST['nmod'])), 'system/classes/n2f.cls.php');
				}

				// If our default error module doesn't exist either, just dump errors and bail
				if (file_exists("modules/{$this->cfg->def_mods->error}/{$filename}") === false) {
					$this->dumpDebug();
					exit;
				} else { // Otherwise, set our one error code and load default error module
					$_REQUEST['error_code'] = N2F_ERRCODE_MODULE_FAILURE;

					require("modules/{$this->cfg->def_mods->error}/{$filename}");
				}
			} else { // Pull in our loader file
				require("modules/{$_REQUEST['nmod']}/{$filename}");
			}

			// Return just so we've returned
			return(null);
		}

		/**
		 * System method for cleaning up loose ends before the script shuts down.
		 *
		 * @return null	Null value, no real return.
		 */
		public function _cleanResources() {
			// Hit our 'notification' event
			$this->hitEvent(N2F_EVT_DESTRUCT, array(&$this));

			// If we're returning a page (ie, not a data call)
			if (!isset($_REQUEST['nret']) || $_REQUEST['nret'] == 'page') {
				// Dump debug if that's what we're told to do
				if ($this->cfg->dbg->dump_debug) {
					$this->dumpDebug();
				}

				// Show our little hidden ad if we're told to
				if ($this->cfg->show_ad !== false) {
					echo("\n\n<!-- Powered by N2 Framework: http://n2framework.com/ -->");
				}
			}

			// Return just so we've returned
			return(null);
		}

		/**
		 * Dumps the current debug data (if present) in a XHTML format.
		 *
		 * @param boolean $return	Boolean value to determine output method.
		 * @return mixed			Mixed value based on site configuration settings (string or null).
		 */
		public function dumpDebug($return = false) {
			// Get current debug arrays
			$errors = $this->debug->getErrors();
			$warnings = $this->debug->getWarnings();
			$notices = $this->debug->getNotices();

			// If we're returning, start buffering
			if ($return) {
				ob_start();
			}

			// Do our display of all three debug message groups

			echo('<div style="padding: 10px">');

			if ($this->debug->showLevel(N2F_DEBUG_ERROR)) {
				if (count($errors) < 1) {
					echo('<div>There were no errors from the system.</div>');
				} else {
					echo('<div>Errors:');

					foreach (array_values($errors) as $error) {
						echo("<br />[E{$error['num']} @ ".date('Y-m-d G:i:s', $error['time'])."] ({$error['file']}) {$error['str']}");
					}

					echo('</div>');
				}
			}

			if ($this->debug->showLevel(N2F_DEBUG_WARN)) {
				if (count($warnings) < 1) {
					echo('<div>There were no warnings from the system.</div>');
				} else {
					echo('<div>Warnings:');

					foreach (array_values($warnings) as $warning) {
						echo("<br />[W{$warning['num']} @ ".date('Y-m-d G:i:s', $warning['time'])."] ({$warning['file']}) {$warning['str']}");
					}

					echo('</div>');
				}
			}

			if ($this->debug->showLevel(N2F_DEBUG_NOTICE)) {
				if (count($notices) < 1) {
					echo('<div>There were no notices from the system.</div>');
				} else {
					echo('<div>Notices:');

					foreach (array_values($notices) as $notice) {
						echo("<br />[N{$notice['num']} @ ".date('Y-m-d G:i:s', $notice['time'])."] ({$notice['file']}) {$notice['str']}");
					}

					echo('</div>');
				}
			}

			echo('</div>');

			// If returning, grab contents after ending output buffering and return them
			if ($return) {
				$contents = ob_get_clean();

				return($contents);
			} else { // Otherwise, just get out of here
				return(null);
			}
		}
	}

	/**
	 * Global configuration class for N2 Framework Yverdon.
	 *
	 */
	class n2f_cfg {
		/**
		 * Holds configuration values for the site.
		 *
		 * @var n2f_cfg_site
		 */
		public $site;
		/**
		 * Holds configuration values for the debug class.
		 *
		 * @var n2f_cfg_dbg
		 */
		public $dbg;
		/**
		 * Holds configuration values for automatically loaded extensions.
		 *
		 * @var array
		 */
		public $auto_exts;
		/**
		 * Holds configuration value for the file structure version to use.
		 *
		 * @var integer
		 */
		public $file_struct;
		/**
		 * Holds configuration value for crypt hash.
		 *
		 * @var string
		 */
		public $crypt_hash;
		/**
		 * Holds configuration values for the default modules.
		 *
		 * @var n2f_cfg_def_mods
		 */
		public $def_mods;
		/**
		 * Holds configuration value for the current system language.
		 *
		 * @var string
		 */
		public $sys_lang;
		/**
		 * Holds configuration value for the current system charset.
		 *
		 * @var string
		 */
		public $charset;
		/**
		 * Holds configuration value for the current system content-type.
		 *
		 * @var string
		 */
		public $content_type;
		/**
		 * Holds configuration value for the toggle of displaying the N2F advert.
		 *
		 * @var boolean
		 */
		public $show_ad;


		/**
		 * Initializes a new n2f_cfg object.
		 *
		 * @param array $cfg	Array of configuration values to use in object.
		 * @return n2f_cfg		The n2f_cfg object, even though PHP doesn't like this.
		 */
		public function __construct(array $cfg = null) {
			// If it's a blank config, setup everything as 'blank'
			if ($cfg === null) {
				$this->site = new n2f_cfg_site();
				$this->dbg = new n2f_cfg_dbg();
				$this->auto_exts = array();
				$this->file_struct = N2F_FS_CURRENT;
				$this->crypt_hash = md5(microtime(true));
				$this->def_mods = new n2f_cfg_def_mods();
				$this->sys_lang = 'en';
				$this->charset = 'utf-8';
				$this->content_type = 'text/html';
				$this->show_ad = true;
			} else { // Otherwise, initialize each one accordingly
				$this->site = (isset($cfg['site'])) ? new n2f_cfg_site($cfg['site']) : new n2f_cfg_site();
				$this->dbg = (isset($cfg['dbg'])) ? new n2f_cfg_dbg($cfg['dbg']) : new n2f_cfg_dbg();
				$this->auto_exts = (isset($cfg['auto_exts'])) ? $cfg['auto_exts'] : array();
				$this->file_struct = (isset($cfg['file_struct'])) ? $cfg['file_struct'] : N2F_FS_CURRENT;
				$this->crypt_hash = (isset($cfg['crypt_hash'])) ? $cfg['crypt_hash'] : md5(microtime(true));
				$this->def_mods = (isset($cfg['def_mods'])) ? new n2f_cfg_def_mods($cfg['def_mods']) : new n2f_cfg_def_mods();
				$this->sys_lang = (isset($cfg['sys_lang'])) ? $cfg['sys_lang'] : 'en';
				$this->charset = (isset($cfg['charset'])) ? $cfg['charset'] : 'utf-8';
				$this->content_type = (isset($cfg['content_type'])) ? $cfg['content_type'] : 'text/html';
				$this->show_ad = (isset($cfg['show_ad'])) ? $cfg['show_ad'] : true;
			}

			// Return for make-believe chaining
			return($this);
		}
	}

	/**
	 * Configuration class for global site values.
	 *
	 */
	class n2f_cfg_site {
		/**
		 * Holds the main domain of the site.
		 *
		 * @var string
		 */
		public $domain;
		/**
		 * Holds the default title of the site.
		 *
		 * @var string
		 */
		public $title;
		/**
		 * Holds the current relative path prefix of the site.
		 *
		 * @var string
		 */
		public $rel_path;
		/**
		 * Holds the current url path suffix of the site.
		 *
		 * @var string
		 */
		public $url_path;
		/**
		 * Holds the site's timezone setting.
		 *
		 * @var string
		 */
		public $timezone;

		/**
		 * Initializes a new n2f_cfg_site object.
		 *
		 * @param array $vals
		 * @return n2f_cfg_site
		 */
		public function __construct(array $vals = null) {
			// If it's a blank config, setup everything as 'blank'
			if ($vals === null) {
				$this->domain = 'somesite.com';
				$this->title = 'Another N2F Site';
				$this->rel_path = './';
				$this->url_path = '/';
				$this->timezone = 'America/New_York';
			} else { // Otherwise, initialize each one accordingly
				$this->domain = (isset($vals['domain'])) ? $vals['domain'] : 'somesite.com';
				$this->title = (isset($vals['title'])) ? $vals['title'] : 'Another N2F Site';
				$this->rel_path = (isset($vals['rel_path'])) ? $vals['rel_path'] : './';
				$this->url_path = (isset($vals['url_path'])) ? $vals['url_path'] : '/';
				$this->timezone = (isset($vals['timezone'])) ? $vals['timezone'] : 'America/New_York';
			}

			// Return for make-believe chaining
			return($this);
		}
	}

	/**
	 * Configuration class for the debug engine.
	 *
	 */
	class n2f_cfg_dbg {
		/**
		 * The current debug engine level.
		 *
		 * @var integer
		 */
		public $level;
		/**
		 * Holds configuration value for debug dump setting.
		 *
		 * @var boolean
		 */
		public $dump_debug;

		/**
		 * Initializes a new n2f_cfg_dbg object.
		 *
		 * @param array $vals
		 * @return n2f_cfg_dbg
		 */
		public function __construct(array $vals = null) {
			// If it's a blank config, setup everything as 'blank'
			if ($vals === null || !isset($vals['level'])) {
				$this->level = N2F_DEBUG_OFF;
				$this->dump_debug = false;
			} else { // Otherwise, initialize each one accordingly
				switch ($vals['level']) {
					case N2F_DEBUG_OFF:
					case N2F_DEBUG_ERROR:
					case N2F_DEBUG_WARN:
					case N2F_DEBUG_NOTICE:
						$this->level = $vals['level'];
						break;
					default:
						$this->level = N2F_DEBUG_OFF;
						break;
				}

				$this->dump_debug = (isset($vals['dump_debug'])) ? $vals['dump_debug'] : false;
			}

			// Return for make-believe chaining
			return($this);
		}
	}

	/**
	 * Configuration class for default modules in the system.
	 *
	 */
	class n2f_cfg_def_mods {
		/**
		 * The default start module for the system.
		 *
		 * @var string
		 */
		public $start;
		/**
		 * The default error module for the system.
		 *
		 * @var string
		 */
		public $error;

		/**
		 * Initializes a new n2f_cfg_def_mods object.
		 *
		 * @param array $vals
		 * @return n2f_cfg_def_mods
		 */
		public function __construct(array $vals = null) {
			// If it's a blank config, setup everything as 'blank'
			if ($vals === null) {
				$this->start = 'main';
				$this->error = 'error';
			} else { // Otherwise, initialize each one accordingly
				$this->start = (isset($vals['start'])) ? $vals['start'] : 'main';
				$this->error = (isset($vals['error'])) ? $vals['error'] : 'error';
			}

			// Return for make-believe chaining
			return($this);
		}
	}

?>