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
	 * $Id: n2f_database.ext.php 194 2012-01-24 20:39:18Z amale@EPSILON $
	 */

	// Our global variable(s)
	global $_n2f_db_extensions, $cfg;

	// Get global instance of n2f_cls
	$n2f = n2f_cls::getInstance();

	// Register extension
	$n2f->registerExtension(
		'n2f_template',
		'n2f_database',
		0.2,
		'Matthew Hykes, Andrew Male',
		'http://n2framework.com/'
	);

	// Pull in library extensions for configuration/etc
	$n2f->requireExtensions(array('n2f_database/config', 'n2f_database/constants', 'n2f_database/lang'));

	// Check if we need to include our template object
	if (is_array($cfg['db']['exts']) && count($cfg['db']['exts']) > 0) {
		foreach (array_values($cfg['db']['exts']) as $ext) {
			$n2f->loadExtension("n2f_database/{$ext}");
		}
	}

	// Hook our initialization callbacks
	$n2f->hookEvent(N2F_EVT_MODULES_LOADED, '_initDatabase');
	$n2f->hookEvent(N2F_EVT_DESTRUCT, '_closeDatabase');

	/**
	 * System method for initializing the database system.
	 *
	 * @return null
	 */
	function _initDatabase() {
		// Our global config array
		global $cfg;

		// If the global config array has the stuff we need
		if (is_array($cfg['db']['exts']) && count($cfg['db']['exts']) > 0 && $cfg['db']['type'] != '') {
			// Pull in the db global variable
			global $db;

			// Set it to the 'default' instance of n2f_database, thus creating the first instance and opening its connection
			$db = n2f_database::setInstance(n2f_cls::getInstance(), $cfg['db']['type']);
			$db->open();
		}

		// Return clean-like
		return(null);
	}

	/**
	 * System method for closing the default database instance.
	 *
	 */
	function _closeDatabase() {
		// Get default instance
		$db = n2f_database::getInstance();

		// If we have it and it's open, close it down
		if ($db instanceof n2f_database && $db->isOpen()) {
			$db->close();
		}
	}

	/**
	 * Core database class for N2 Framework Yverdon.
	 *
	 */
	class n2f_database extends n2f_events {
		/**
		 * Connection resource for this n2f_database object.
		 *
		 * @var resource
		 */
		public $conn;
		/**
		 * Number of queries performed by this n2f_database object.
		 *
		 * @var integer
		 */
		public $queries;
		/**
		 * The extension currently in use by this n2f_database object.
		 *
		 * @var string
		 */
		public $extension;
		/**
		 * The current configuration to use when opening the connection.
		 *
		 * @var n2f_cfg_db
		 */
		protected $_config;
		/**
		 * Internal container for object data.
		 *
		 * @var array
		 */
		protected $_internalData;
		/**
		 * Protected static property to hold the stack of stored queries.
		 *
		 * @var array
		 */
		protected static $_storedQueries = array();
		/**
		 * Protected static property to hold current singleton global.
		 *
		 * @var array
		 */
		protected static $_instance = array();
		/**
		 * List of callbacks used by the core system for engine calls.
		 *
		 * @var array
		 */
		private static $callbacks = array(
				N2F_DBEVT_OPEN_CONNECTION,
				N2F_DBEVT_CLOSE_CONNECTION,
				N2F_DBEVT_CHECK_CONNECTION,
				N2F_DBEVT_ADD_PARAMETER,
				N2F_DBEVT_EXECUTE_QUERY,
				N2F_DBEVT_GET_ROW,
				N2F_DBEVT_GET_ROWS,
				N2F_DBEVT_GET_LAST_INC,
				N2F_DBEVT_GET_NUMROWS,
				N2F_DBEVT_GET_RESULT
		);


		/**
		 * Method to get a current n2f_database instance.
		 *
		 * @param string $key	String value of key name for n2f_database global.
		 * @return n2f_database	The requested n2f_database global, 'null' if non-existant.
		 */
		public static function &getInstance($key = null) {
			// If we don't have an instance yet, let's set it as 'blank'
			if ($key !== null && !isset(n2f_database::$_instance[$key])) {
				n2f_database::$_instance[$key] = new n2f_database(n2f_cls::getInstance(), '');
			} else if ($key === null || empty($key)) { // Otherwise, if we don't have our instance key set, we want default
				$key = 'default';

				// If the default instance isn't set, go ahead and set that up
				if (!isset(n2f_database::$_instance['default'])) {
					n2f_database::$_instance['default'] = new n2f_database(n2f_cls::getInstance(), '');
				}
			}

			// Store in memory
			$instance = n2f_database::$_instance[$key];

			// Return the memorized...thing
			return($instance);
		}

		/**
		 * Method to set a current n2f_database instance.
		 *
		 * @param n2f_cls $n2f	An n2f_cls object with configuration values.
		 * @param string $ext	String value for the extension type.
		 * @param boolean $new	Boolean value determining if this is a new global.
		 * @param string $key	String value for a new global.
		 * @return n2f_database	The new n2f_database global.
		 */
		public static function &setInstance(n2f_cls &$n2f, $ext, $new = false, $key = null, n2f_cfg_db $cfg = null) {
			// If we have a valid unique key, set that bugger up
			if ($new === true && $key !== null && !empty($key)) {
				n2f_database::$_instance[$key] = new n2f_database($n2f, $ext, $cfg);
				$instance = n2f_database::$_instance[$key];
			} else { // Otherwise, set it up as the default instance
				n2f_database::$_instance['default'] = new n2f_database($n2f, $ext, $cfg);
				$instance = n2f_database::$_instance['default'];
			}

			// And return that instance
			return($instance);
		}

		/**
		 * Static function for adding database extensions to the available list.
		 *
		 * @param string $name		Name of extension to add
		 * @param array $callbacks	Array of callbacks for extension
		 * @return null
		 */
		public static function addExtension($name, array $callbacks) {
			// If no name given, I assure you we're not interested
			if (empty($name)) {
				return(null);
			}

			// If the callback count is incorrect, we're still not interested
			if (count($callbacks) != count(self::$callbacks)) {
				return(null);
			}

			// If we're not finding all of our required callbacks, again not interested
			foreach (array_values(self::$callbacks) as $callbk) {
				if (!isset($callbacks[$callbk])) {
					return(null);
				}
			}

			// Pull in our global extension array
			global $_n2f_db_extensions;

			// Fill in our details for the extension
			$_n2f_db_extensions[$name] = array(
				N2F_DBEVT_OPEN_CONNECTION		=> $callbacks[N2F_DBEVT_OPEN_CONNECTION],
				N2F_DBEVT_CLOSE_CONNECTION		=> $callbacks[N2F_DBEVT_CLOSE_CONNECTION],
				N2F_DBEVT_CHECK_CONNECTION		=> $callbacks[N2F_DBEVT_CHECK_CONNECTION],
				N2F_DBEVT_ADD_PARAMETER			=> $callbacks[N2F_DBEVT_ADD_PARAMETER],
				N2F_DBEVT_EXECUTE_QUERY			=> $callbacks[N2F_DBEVT_EXECUTE_QUERY],
				N2F_DBEVT_GET_ROW				=> $callbacks[N2F_DBEVT_GET_ROW],
				N2F_DBEVT_GET_ROWS				=> $callbacks[N2F_DBEVT_GET_ROWS],
				N2F_DBEVT_GET_LAST_INC			=> $callbacks[N2F_DBEVT_GET_LAST_INC],
				N2F_DBEVT_GET_NUMROWS			=> $callbacks[N2F_DBEVT_GET_NUMROWS],
				N2F_DBEVT_GET_RESULT			=> $callbacks[N2F_DBEVT_GET_RESULT]
			);

			// Return quietly
			return(null);
		}

		/**
		 * Static function for storing a query in the stack.
		 *
		 * @param string $key		String value of key for recalling the query.
		 * @param string $engine		String value of the database engine this query targets.
		 * @param string $sql		String value of the query.
		 * @param array $paramTypes	Optional array of parameter values to parameterize the query.
		 * @param mixed $options		Optional mixed value of query options.
		 * @return boolean			Boolean TRUE or FALSE based on storage success.
		 */
		public static function storeQuery($key, $engine, $sql, array $paramTypes = null, $options = null) {
			// Initialize the return variable
			$ret = true;

			// If the engine doesn't exist yet, add it and add our query
			if (!isset(n2f_database::$_storedQueries[$engine])) {
				n2f_database::$_storedQueries[$engine] = array($key => array($sql, $paramTypes, $options));
			} else if (isset(n2f_database::$_storedQueries[$engine][$key])) { // Otherwise, we aren't setting so return failure
				$ret = false;
			} else { // Toss the query onto the pile
				n2f_database::$_storedQueries[$engine][$key] = array($sql, $paramTypes, $options);
			}

			// Return our success (or lack thereof)
			return($ret);
		}


		/**
		 * Initializes a new n2f_database object.
		 *
		 * @param n2f_cls $n2f	n2f_cls instance to use for stuff.
		 * @param string $ext	String of extension key to use.
		 * @return n2f_database	n2f_database instance for make-believe chaining.
		 */
		public function __construct(n2f_cls &$n2f, $ext, n2f_cfg_db $cfg = null) {
			// Pull in global extension list and initialize our event sub-system
			global $_n2f_db_extensions;
			parent::__construct();

			// Initialize our variables
			$this->conn = null;
			$this->queries = 0;
			$this->_config = $cfg;
			$this->_internalData = array();

			// Register our internal events
			$this->addEvent(N2F_DBEVT_CONNECTION_OPENED);
			$this->addEvent(N2F_DBEVT_CONNECTION_CLOSED);
			$this->addEvent(N2F_DBEVT_QUERY_CREATED);
			$this->addEvent(N2F_DBEVT_OPEN_CONNECTION, true);
			$this->addEvent(N2F_DBEVT_CLOSE_CONNECTION, true);
			$this->addEvent(N2F_DBEVT_CHECK_CONNECTION, true);

			// If the extension isn't in our list
			if (!isset($_n2f_db_extensions[$ext])) {
				// If we're throwing errors, throw the error
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(N2F_ERROR_DB_EXTENSION_NOT_LOADED, S('N2F_ERROR_DB_EXTENSION_NOT_LOADED', array($ext)), 'n2f_database.ext.php');
				}

				// Set the extension to 'null' so it's recognizable we failed
				$this->extension = null;
			} else { // Otherwise, hook our callbacks and set our extension
				$this->hookEvent(N2F_DBEVT_OPEN_CONNECTION, $_n2f_db_extensions[$ext][N2F_DBEVT_OPEN_CONNECTION]);
				$this->hookEvent(N2F_DBEVT_CLOSE_CONNECTION, $_n2f_db_extensions[$ext][N2F_DBEVT_CLOSE_CONNECTION]);
				$this->hookEvent(N2F_DBEVT_CHECK_CONNECTION, $_n2f_db_extensions[$ext][N2F_DBEVT_CHECK_CONNECTION]);
				$this->extension = $ext;
			}

			// Return for make-believe chaining
			return($this);
		}

		/**
		 * Mechanism for storing data in the n2f_database object's internal data container.
		 *
		 * @param mixed $key	String value of key name for data.
		 * @param mixed $data	Mixed value of data.
		 * @return n2f_database	n2f_database object for chaining.
		 */
		public function addData($key, $data) {
			// Add the data to our lil stack
			$this->_internalData[$key] = $data;

			// Return for chaining
			return($this);
		}

		/**
		 * Retrieves data from the n2f_database object's internal data container.
		 *
		 * @param mixed $key	String value of key name for data.
		 * @return mixed		Mixed value of stored data.
		 */
		public function getData($key) {
			// If the data is on the stack, return it
			if (isset($this->_internalData[$key])) {
				return($this->_internalData[$key]);
			}

			// Otherwise, fail gracefully
			return(null);
		}

		/**
		 * Opens the connection for this n2f_database object.
		 *
		 * @return n2f_database	n2f_database object for chaining.
		 */
		public function open() {
			// Grab instance of n2f_cls
			$n2f = n2f_cls::getInstance();

			// If we have no current configuration, grab the global one
			if ($this->_config === null) {
				$this->_config = new n2f_cfg_db($GLOBALS['cfg']['db']);
			}

			// Hit the open and open-notify events, respectively
			$this->hitEvent(N2F_DBEVT_OPEN_CONNECTION, array(&$this, $this->_config));
			$this->hitEvent(N2F_DBEVT_CONNECTION_OPENED, array(&$this));

			// If we're throwing notices, throw the notice
			if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
				$n2f->debug->throwNotice(N2F_NOTICE_DB_CONNECTION_OPENED, S('N2F_NOTICE_DB_CONNECTION_OPENED', array($this->extension)), 'n2f_database.ext.php');
			}

			// Return for chaining
			return($this);
		}

		/**
		 * Closes the connection for this n2f_database object.
		 *
		 * @return n2f_database	n2f_database object for chaining.
		 */
		public function close() {
			// Grab instance of n2f_cls
			$n2f = n2f_cls::getInstance();

			// Hit the close and close-notify events, respectively
			$this->hitEvent(N2F_DBEVT_CLOSE_CONNECTION, array(&$this));
			$this->hitEvent(N2F_DBEVT_CONNECTION_CLOSED, array(&$this));
			$this->conn = null;

			// If we're throwing notices, throw the notice
			if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
				$n2f->debug->throwNotice(N2F_NOTICE_DB_CONNECTION_CLOSED, S('N2F_NOTICE_DB_CONNECTION_CLOSED', array($this->extension)), 'n2f_database.ext.php');
			}

			// Return for chaining
			return($this);
		}

		/**
		 * Returns true or false based on whether or not the object's connection is active.
		 *
		 * @return boolean	Boolean TRUE or FALSE based on engine's response to connection-check.
		 */
		public function isOpen() {
			// Hit the check-connection event and return the result
			return((bool)$this->hitEvent(N2F_DBEVT_CHECK_CONNECTION, array(&$this)));
		}

		/**
		 * Produces a new n2f_database_query object from the given query.
		 *
		 * @param string $sql		String value of query to execute in engine.
		 * @return n2f_database_query	n2f_database_query object representing the query.
		 */
		public function query($sql, $options = null) {
			// Create new query object and hit notification event
			$result = new n2f_database_query($sql, $this, $options);
			$this->hitEvent(N2F_DBEVT_QUERY_CREATED, array(&$result, &$this));

			// Return the query object
			return($result);
		}

		/**
		 * Produces a new n2f_database_query object (or null on failure) using the requested stored query.
		 *
		 * @param string $key		String value of the key of the stored query to call.
		 * @param array $params		Optional array of parameter keys and values (format: 'key' => $val)
		 * @param array $replacements	Optional array of replacement values for use in structuring the query.
		 * @return n2f_database_query	n2f_database_query object returned by call to n2f_database::query(), null if stored query couldn't be found.
		 */
		public function storedQuery($key, array $params = null, array $replacements = null) {
			// Grab instance of n2f_cls
			$n2f = n2f_cls::getInstance();

			// If the extension or the stored query aren't available...
			if (!isset(n2f_database::$_storedQueries[$this->extension]) || !isset(n2f_database::$_storedQueries[$this->extension][$key])) {
				// If we're showing errors, throw the error
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(N2F_ERROR_DB_INVALID_STORED_QUERY, S('N2F_ERROR_DB_INVALID_STORED_QUERY', array($key)), 'n2f_database.ext.php');
				}

				// Return null to invalidate
				return(null);
			}

			// Grab the query information from storage
			$stored = n2f_database::$_storedQueries[$this->extension][$key];

			// If there are replacements provided
			if ($replacements !== null && count($replacements) > 0) {
				// Loop through each one and run a str_replace() on the stored query
				foreach ($replacements as $key => $val) {
					$stored[0] = str_replace("_%{$key}%_", $val, $stored[0]);
				}
			}

			// Send off the query for creation
			$query = $this->query($stored[0], $stored[2]);

			// If there are parameters to pass and the counts are the same
			if (count($stored[1]) > 0 && count($stored[1]) == count($params)) {
				$i = 0;

				// Loop through parameters, adding them to the query properly
				foreach ($params as $key => $val) {
					$query->addParam($key, $val, $stored[1][$i]);
					$i++;
				}
			} else if (count($stored[1]) > 0 || count($params) > 0) { // If the counts are wrong but there are ones to count
				// If we're throwing warnings, throw the warning
				if ($n2f->debug->showLevel(N2F_DEBUG_WARN)) {
					$n2f->debug->throwWarning(N2F_WARN_DB_INCORRECT_STORED_PARAMETER_COUNT, S('N2F_WARN_DB_INCORRECT_STORED_PARAMETER_COUNT'), 'n2f_database.ext.php');
				}
			}

			// Return the query object
			return($query);
		}
	}

	/**
	 * Core database query class for N2 Framework Yverdon.
	 *
	 */
	class n2f_database_query extends n2f_events {
		/**
		 * Internal reference to the global database handler.
		 *
		 * @var n2f_database
		 */
		public $db;
		/**
		 * SQL string used for this query.
		 *
		 * @var string
		 */
		public $query;
		/**
		 * Collection of parameters for the current query.
		 *
		 * @var array
		 */
		public $params;
		/**
		 * Holds the current result set for the query if applicable.
		 *
		 * @var result
		 */
		public $result;
		/**
		 * Latest error returned by the query.
		 *
		 * @var array
		 */
		private $_errors;
		/**
		 * Internal container for object data.
		 *
		 * @var array
		 */
		protected $_internalData;

		/**
		 * Initializes a new n2f_database_query object.
		 *
		 * @param string $sql		String value of query to execute through the engine.
		 * @param n2f_database $db	n2f_database object which triggered creation of this query object.
		 * @return n2f_database_query	n2f_database_query object for make-believe chaining.
		 */
		public function __construct($sql, n2f_database &$db, $options = null) {
			// Global stacks, event sub-system and variable initialization
			global $_n2f_db_extensions;
			$n2f = n2f_cls::getInstance();
			parent::__construct();
			$this->db = $db;
			$this->query = $sql;
			$this->errors = array();
			$this->params = array();
			$this->result = null;
			$this->_internalData = array();

			// Add any supplied options to internal storage
			$this->addData('options', $options);

			// Register our internal events
			$this->addEvent(N2F_DBEVT_PARAMETER_ADDED);
			$this->addEvent(N2F_DBEVT_QUERY_EXECUTED);
			$this->addEvent(N2F_DBEVT_ROW_RETRIEVED);
			$this->addEvent(N2F_DBEVT_ROWS_RETRIEVED);
			$this->addEvent(N2F_DBEVT_LAST_INC_RETRIEVED);
			$this->addEvent(N2F_DBEVT_NUMROWS_RETRIEVED);
			$this->addEvent(N2F_DBEVT_RESULT_RETRIEVED);
			$this->addEvent(N2F_DBEVT_ADD_PARAMETER, true);
			$this->addEvent(N2F_DBEVT_EXECUTE_QUERY, true);
			$this->addEvent(N2F_DBEVT_GET_ROW, true);
			$this->addEvent(N2F_DBEVT_GET_ROWS, true);
			$this->addEvent(N2F_DBEVT_GET_LAST_INC, true);
			$this->addEvent(N2F_DBEVT_GET_NUMROWS, true);
			$this->addEvent(N2F_DBEVT_GET_RESULT, true);

			// If we don't have a chosen extension
			if ($db->extension === null || empty($db->extension)) {
				// If we're throwing errors, throw the error
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(N2F_ERROR_DB_EXTENSION_EMPTY, S('N2F_ERROR_DB_EXTENSION_EMPTY'), 'n2f_database.ext.php');
				}

				// Add an error to the internal collection
				$this->addError(S('N2F_ERROR_DB_EXTENSION_EMPTY'));
			} else if ($db->isOpen() !== true) { // If the database isn't open
				// If we're throwing errors, throw the error
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(N2F_ERROR_DB_NOT_LOADED, S('N2F_ERROR_DB_NOT_LOADED'), 'n2f_database.ext.php');
				}

				// Add an error to the internal collection
				$this->addError(S('N2F_ERROR_DB_NOT_LOADED'));
			} else { // We're in the clear, hook all of our callbacks for the engine
				$this->hookEvent(N2F_DBEVT_ADD_PARAMETER, $_n2f_db_extensions[$db->extension][N2F_DBEVT_ADD_PARAMETER]);
				$this->hookEvent(N2F_DBEVT_EXECUTE_QUERY, $_n2f_db_extensions[$db->extension][N2F_DBEVT_EXECUTE_QUERY]);
				$this->hookEvent(N2F_DBEVT_GET_ROW, $_n2f_db_extensions[$db->extension][N2F_DBEVT_GET_ROW]);
				$this->hookEvent(N2F_DBEVT_GET_ROWS, $_n2f_db_extensions[$db->extension][N2F_DBEVT_GET_ROWS]);
				$this->hookEvent(N2F_DBEVT_GET_LAST_INC, $_n2f_db_extensions[$db->extension][N2F_DBEVT_GET_LAST_INC]);
				$this->hookEvent(N2F_DBEVT_GET_NUMROWS, $_n2f_db_extensions[$db->extension][N2F_DBEVT_GET_NUMROWS]);
				$this->hookEvent(N2F_DBEVT_GET_RESULT, $_n2f_db_extensions[$db->extension][N2F_DBEVT_GET_RESULT]);
			}

			// If we're throwing notices, throw the notice
			if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
				$n2f->debug->throwNotice(N2F_NOTICE_DB_QUERY_CREATED, S('N2F_NOTICE_DB_QUERY_CREATED', array($db->extension, $sql)), 'n2f_database.ext.php');
			}

			// Return for make-believe chaining
			return($this);
		}

		/**
		 * Mechanism for storing data in the n2f_database_query object's internal data container.
		 *
		 * @param mixed $key	Mixed value of key for stored data.
		 * @param mixed $data	Mixed value of stored data.
		 * @return n2f_database	n2f_database object for chaining.
		 */
		public function addData($key, $data) {
			// Store the data in our internal collection
			$this->_internalData[$key] = $data;

			// Return for chaining
			return($this);
		}

		/**
		 * Retrieves data from the n2f_database_query object's internal data container.
		 *
		 * @param mixed $key	Mixed value of key for stored data.
		 * @return mixed		Mixed value of stored data.
		 */
		public function getData($key) {
			// If the data exists in our collection, return it
			if (isset($this->_internalData[$key])) {
				return($this->_internalData[$key]);
			}

			// Return blank, not here
			return(null);
		}

		/**
		 * Adds a parameter to the query stack.
		 *
		 * @param string $key		String value of parameter key.
		 * @param mixed $value		Mixed value of parameter value.
		 * @param mixed $type		Mixed value of parameter type.
		 * @return n2f_database_query	n2f_database_query object for chaining.
		 */
		public function addParam($key, $value, $type) {
			// Grab instance of n2f_cls
			$n2f = n2f_cls::getInstance();

			// Hit the add-param event to do whatever
			$result = $this->hitEvent(N2F_DBEVT_ADD_PARAMETER, array(&$this, $key, $value, $type));

			// If it didn't fail and we're throwing notices, throw the notice
			if ($result !== false && $n2f->cfg->dbg->level >= N2F_DEBUG_NOTICE) {
				$n2f->debug->throwNotice(N2F_NOTICE_DB_PARAMETER_ADDED, S('N2F_NOTICE_DB_PARAMETER_ADDED', array($key)), 'n2f_database.ext.php');
			}

			// Return for chaining
			return($this);
		}

		/**
		 * Adds an array of parameters to the query stack.
		 *
		 * @param array $params		Array of parameters to add to query.
		 * @return n2f_database_query	n2f_database_query object for chaining.
		 */
		public function addParams(array $params) {
			// Grab instance of n2f_cls
			$n2f = n2f_cls::getInstance();

			// If parameter count is 0
			if (count($params) < 1) {
				// If we're throwing warnings, throw the warning
				if ($n2f->debug->showLevel(N2F_DEBUG_WARN)) {
					$n2f->debug->throwWarning(N2F_WARN_DB_PARAMETERS_NOT_SUPPLIED, S('N2F_WARN_DB_PARAMETERS_NOT_SUPPLIED', array($this->query)), 'n2f_database.ext.php');
				}

				// Return for chaining
				return($this);
			}

			// Loop through each supplied parameter
			foreach (array_values($params) as $param) {
				// If the element count isn't right
				if (count($param) != 3) {
					// If we're throwing warnings, throw the warning
					if ($n2f->debug->showLevel(N2F_DEBUG_WARN)) {
						$n2f->debug->throwWarning(N2F_WARN_DB_INVALID_PARAMETER, S('N2F_WARN_DB_INVALID_PARAMETER', array(debugEcho($param))), 'n2f_database.ext.php');
					}

					// Just move to the next in the list
					continue;
				}

				// Call addParam() for this one
				$this->addParam($param[0], $param[1], $param[2]);
			}

			// Return for chaining
			return($this);
		}

		/**
		 * Executes the query.
		 *
		 * @return n2f_database_query	n2f_database_query object for chaining.
		 */
		public function execQuery() {
			// Grab instance of n2f_cls
			$n2f = n2f_cls::getInstance();

			// If there aren't any errors, we should be good to try
			if (count($this->_errors) < 1) {
				// Hit the exec-query event and increment our query count
				$this->hitEvent(N2F_DBEVT_EXECUTE_QUERY, array(&$this));
				$this->db->queries += 1;

				// If we're throwing notices, throw the notice
				if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
					$n2f->debug->throwNotice(N2F_NOTICE_DB_QUERY_EXECUTED, S('N2F_NOTICE_DB_QUERY_EXECUTED'), 'n2f_database.ext.php');
				}
			}

			// Return for chaining
			return($this);
		}

		/**
		 * Adds an error to the n2f_database_query object's error stack.
		 *
		 * @param string $string		String value to add to error collection.
		 * @return n2f_database_query	n2f_database_query object for chaining.
		 */
		public function addError($string) {
			// If the string is empty, just return
			if (empty($string)) {
				return($this);
			}

			// Add to the collection
			$this->_errors[] = $string;

			// Return for chaining
			return($this);
		}

		/**
		 * Returns true or false based on whether or not an error has occurred.
		 *
		 * @return boolean	Boolean TRUE or FALSE based on presence of errors in the internal collection.
		 */
		public function isError() {
			// If there is something in the collection, there's an error
			if (count($this->_errors) > 0) {
				return(true);
			}

			// Return false, doesn't appear there are errors
			return(false);
		}

		/**
		 * Returns the last populated error string.
		 *
		 * @return string	String value of latest error in internal collection.
		 */
		public function fetchError() {
			// If we don't have any errors, don't worry about it
			if (count($this->_errors) < 1) {
				return(null);
			}

			// Return the last one in the collection
			return($this->_errors[count($this->_errors) - 1]);
		}

		/**
		 * Returns the error stack.
		 *
		 * @return array	Array of the error collection.
		 */
		public function fetchErrors() {
			return($this->_errors);
		}

		/**
		 * Fetches a single row from the result.
		 *
		 * @return mixed	Mixed value of next row in result set.
		 */
		public function fetchRow() {
			return($this->hitEvent(N2F_DBEVT_GET_ROW, array(&$this)));
		}

		/**
		 * Fetches all rows from the result.
		 *
		 * @return mixed	Mixed value of all rows in the result set.
		 */
		public function fetchRows() {
			return($this->hitEvent(N2F_DBEVT_GET_ROWS, array(&$this)));
		}

		/**
		 * Fetches a specific field from the result.
		 *
		 * @param integer $offset	Integer value of row offset in result set.
		 * @param string $field_name	String value of field name to pull from row.
		 * @return mixed			Mixed value of result pulled from row.
		 */
		public function fetchResult($offset, $field_name) {
			return($this->hitEvent(N2F_DBEVT_GET_RESULT, array(&$this, $offset, $field_name)));
		}

		/**
		 * Fetches the last automatically incremented value from the query (if applicable).
		 *
		 * @return mixed	Mixed value of last auto_increment value from query.
		 */
		public function fetchInc($params = null) {
			// If we have parameters to pass, pass and return result
			if ($params !== null) {
				return($this->hitEvent(N2F_DBEVT_GET_LAST_INC, array(&$this, $params)));
			}

			// Otherwise, just return the result
			return($this->hitEvent(N2F_DBEVT_GET_LAST_INC, array(&$this)));
		}

		/**
		 * Returns the number of rows from the result.
		 *
		 * @return integer	Integer value of the number of rows in the result set.
		 */
		public function numRows() {
			return($this->hitEvent(N2F_DBEVT_GET_NUMROWS, array(&$this)));
		}

		/**
		 * Determines if a provided type is a valid generic type.
		 *
		 * @param integer $type	Integer value to test as parameter type.
		 * @return boolean		Boolean TRUE or FALSE based on the type's validity.
		 */
		public function validParamType($type) {
			// If the type value isn't appropriate, fail
			if ($type < 0 || $type > 5) {
				return(false);
			}

			// Succeed
			return(true);
		}
	}

	/**
	 * Configuration class for database class and extensions.
	 *
	 */
	class n2f_cfg_db {
		/**
		 * The current type for the database engine.
		 *
		 * @var string
		 */
		public $type;
		/**
		 * The current host for the database engine.
		 *
		 * @var string
		 */
		public $host;
		/**
		 * The current databse name for the database engine.
		 *
		 * @var string
		 */
		public $name;
		/**
		 * The current username for the database engine.
		 *
		 * @var string
		 */
		public $user;
		/**
		 * The current user password for the database engine.
		 *
		 * @var string
		 */
		public $pass;
		/**
		 * The current port number for the database engine.
		 *
		 * @var integer
		 */
		public $port;
		/**
		 * The current socket for the database engine.
		 *
		 * @var string
		 */
		public $sock;
		/**
		 * The current filename for the database engine.
		 *
		 * @var string
		 */
		public $file;
		/**
		 * The current mode for the database engine.
		 *
		 * @var mixed
		 */
		public $mode;

		/**
		 * Initializes a new n2f_cfg_db object.
		 *
		 * @param array $vals	Optional array of configuration values.
		 * @return n2f_cfg_db	n2f_cfg_db object for make-believe chaining.
		 */
		public function __construct(array $vals = null) {
			// Initialize values in default state
			if ($vals === null) {
				$this->type = null;
				$this->host = null;
				$this->name = null;
				$this->user = null;
				$this->pass = null;
				$this->port = null;
				$this->sock = null;
				$this->file = null;
				$this->mode = null;
			} else { // Otherwise try using the supplied values
				$this->type = (isset($vals['type'])) ? $vals['type'] : null;
				$this->host = (isset($vals['host'])) ? $vals['host'] : null;
				$this->name = (isset($vals['name'])) ? $vals['name'] : null;
				$this->user = (isset($vals['user'])) ? $vals['user'] : null;
				$this->pass = (isset($vals['pass'])) ? $vals['pass'] : null;
				$this->port = (isset($vals['port'])) ? $vals['port'] : null;
				$this->sock = (isset($vals['sock'])) ? $vals['sock'] : null;
				$this->file = (isset($vals['file'])) ? $vals['file'] : null;
				$this->mode = (isset($vals['mode'])) ? $vals['mode'] : null;
			}

			// Return for make-believe chaining
			return($this);
		}
	}

?>