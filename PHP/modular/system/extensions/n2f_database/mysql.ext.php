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
	 * $Id: mysql.ext.php 195 2012-01-25 17:21:16Z amale@EPSILON $
	 */

	// Global variable(s)
	$n2f = n2f_cls::getInstance();

	// Error constants
	define('MYSQLDB_ERROR_INVALID_CONFIG',				'0001');
	define('MYSQLDB_ERROR_FAILED_DBSELECT',				'0002');
	define('MYSQLDB_ERROR_FAILED_DBCONNECT',			'0003');
	define('MYSQLDB_ERROR_FAILED_QUERY',				'0004');
	define('MYSQLDB_ERROR_NO_QUERY',					'0005');
	define('MYSQLDB_ERROR_MISSING_MYSQL',				'0006');

	// English error strings
	L('en', 'MYSQLDB_ERROR_INVALID_CONFIG',				'The configuration submitted for the MySQL database extension was invalid.');
	L('en', 'MYSQLDB_ERROR_FAILED_DBSELECT',			"The MySQL database extension was unable to select the '_%1%_' database for the following reason(s): _%2%_");
	L('en', 'MYSQLDB_ERROR_FAILED_DBCONNECT',			"The MySQL database extension was unable to connect for the following reason(s): _%1%_");
	L('en', 'MYSQLDB_ERROR_FAILED_QUERY',				'Failed to execute a query. (_%1%_)');
	L('en', 'MYSQLDB_ERROR_NO_QUERY',					'Operation performed on a non-query.');
	L('en', 'MYSQLDB_ERROR_MISSING_MYSQL',				'The PHP MySQL extension is not installed on this system, aborting MySQL extension registration.');

	// German error strings
	L('de', 'MYSQLDB_ERROR_INVALID_CONFIG',				'The configuration submitted for the MySQL database extension was invalid.');
	L('de', 'MYSQLDB_ERROR_FAILED_DBSELECT',			"The MySQL database extension was unable to select the '_%1%_' database for the following reason(s): _%2%_");
	L('de', 'MYSQLDB_ERROR_FAILED_DBCONNECT',			"The MySQL database extension was unable to connect for the following reason(s): _%1%_");
	L('de', 'MYSQLDB_ERROR_FAILED_QUERY',				'Failed to execute a query. (_%1%_)');
	L('de', 'MYSQLDB_ERROR_NO_QUERY',					'Operation performed on a non-query.');
	L('de', 'MYSQLDB_ERROR_MISSING_MYSQL',				'The PHP MySQL extension is not installed on this system, aborting MySQL extension registration.');

	// Spanish error strings
	L('es', 'MYSQLDB_ERROR_INVALID_CONFIG',				'The configuration submitted for the MySQL database extension was invalid.');
	L('es', 'MYSQLDB_ERROR_FAILED_DBSELECT',			"The MySQL database extension was unable to select the '_%1%_' database for the following reason(s): _%2%_");
	L('es', 'MYSQLDB_ERROR_FAILED_DBCONNECT',			"The MySQL database extension was unable to connect for the following reason(s): _%1%_");
	L('es', 'MYSQLDB_ERROR_FAILED_QUERY',				'Failed to execute a query. (_%1%_)');
	L('es', 'MYSQLDB_ERROR_NO_QUERY',					'Operation performed on a non-query.');
	L('es', 'MYSQLDB_ERROR_MISSING_MYSQL',				'The PHP MySQL extension is not installed on this system, aborting MySQL extension registration.');

	// Swedish error strings
	L('se', 'MYSQLDB_ERROR_INVALID_CONFIG',				'The configuration submitted for the MySQL database extension was invalid.');
	L('se', 'MYSQLDB_ERROR_FAILED_DBSELECT',			"The MySQL database extension was unable to select the '_%1%_' database for the following reason(s): _%2%_");
	L('se', 'MYSQLDB_ERROR_FAILED_DBCONNECT',			"The MySQL database extension was unable to connect for the following reason(s): _%1%_");
	L('se', 'MYSQLDB_ERROR_FAILED_QUERY',				'Failed to execute a query. (_%1%_)');
	L('se', 'MYSQLDB_ERROR_NO_QUERY',					'Operation performed on a non-query.');
	L('se', 'MYSQLDB_ERROR_MISSING_MYSQL',				'The PHP MySQL extension is not installed on this system, aborting MySQL extension registration.');

	// Create our array of handlers
	$handlers = array(
		N2F_DBEVT_OPEN_CONNECTION					=> 'mysql_openConnection',
		N2F_DBEVT_CLOSE_CONNECTION					=> 'mysql_closeConnection',
		N2F_DBEVT_CHECK_CONNECTION					=> 'mysql_checkConnection',
		N2F_DBEVT_ADD_PARAMETER						=> 'mysql_addParameter',
		N2F_DBEVT_EXECUTE_QUERY						=> 'mysql_executeQuery',
		N2F_DBEVT_GET_ROW							=> 'mysql_getRow',
		N2F_DBEVT_GET_ROWS							=> 'mysql_getRows',
		N2F_DBEVT_GET_LAST_INC						=> 'mysql_getLastInc',
		N2F_DBEVT_GET_NUMROWS						=> 'mysql_getNumRows',
		N2F_DBEVT_GET_RESULT						=> 'mysql_getResult'
	);

	// Check that the MySQL library is available
	if (function_exists('mysql_connect')) {
		// Add our callbacks to the extension list
		n2f_database::addExtension('mysql', $handlers);

		// Register the extension with the core
		$n2f->registerExtension(
			'n2f_database/mysql',
			'n2f_mysql_database',
			'0.1.1',
			'Andrew Male',
			'http://n2framework.com/'
		);
	} else { // If it isn't available, we certainly aren't going to try working
		// If we're throwing errors, throw the error
		if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
			$n2f->debug->throwError(MYSQLDB_ERROR_MISSING_MYSQL, S('MYSQLDB_ERROR_MISSING_MYSQL'), 'n2f_database/mysql.ext.php');
		}
	}

	/**
	 * MySQL extension handler for opening a database connection.
	 *
	 * @param n2f_database $db	Current n2f_database object calling the handler
	 * @param n2f_cfg_db $cfg	Configuration value from n2f_cls::cfg for n2f_database object
	 * @return boolean			Boolean TRUE or FALSE based on connection's success.
	 */
	function mysql_openConnection(n2f_database &$db, n2f_cfg_db $cfg) {
		// Grab instance of n2f_cls
		$n2f = n2f_cls::getInstance();

		// If we haven't defined our cache check, do so now
		if (!defined('MYSQLDB_CAN_CACHE')) {
			define('MYSQLDB_CAN_CACHE',		(bool)$n2f->hasExtension('n2f_cache'));
		}

		// If we are missing any required connection values
		if (!isset($cfg->host) || !isset($cfg->name) || !isset($cfg->user) || !isset($cfg->pass)) {
			// If we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLDB_ERROR_INVALID_CONFIG, S('MYSQLDB_ERROR_INVALID_CONFIG'), 'n2f_database/mysql.ext.php');
			}

			// Set the connection to false so it tests badly
			$db->conn = false;

			// And return false to say we had issues
			return(false);
		} else { // Otherwise, we have what we need to try connecting
			// Try connecting
			$db->conn = @mysql_connect($cfg->host, $cfg->user, $cfg->pass, true);

			// If we're open (ie, connect worked)
			if ($db->isOpen()) {
				// Try selecting the database
				if (!@mysql_select_db($cfg->name, $db->conn)) {
					// If we're throwing errors, throw the error
					if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
						$n2f->debug->throwError(MYSQLDB_ERROR_FAILED_DBSELECT, S('MYSQLDB_ERROR_FAILED_DBSELECT', array($cfg->name, mysql_error())), 'n2f_database/mysql.ext.php');
					}

					// This means the connection failed too...in a way
					$db->conn = false;

					// Return failure, drink it in
					return(false);
				}
			} else { // We aren't open, that's a problem
				// If we're throwing errors, throw the error
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(MYSQLDB_ERROR_FAILED_DBCONNECT, S('MYSQLDB_ERROR_FAILED_DBCONNECT', array(mysql_error())), 'n2f_database/mysql.ext.php');
				}

				// And return a failure
				return(false);
			}
		}

		// Oh we're good, just live your life
		return(true);
	}

	/**
	 * MySQL extension handler for closing a database connection.
	 *
	 * @param n2f_database $db	Current n2f_database object calling the handler
	 * @return boolean			Boolean TRUE or FALSE based on close's success.
	 */
	function mysql_closeConnection(n2f_database &$db) {
		// If the thing isn't even open, just fail
		if ($db->isOpen() === false) {
			return(false);
		}

		// Silently try closing
		@mysql_close($db->conn);

		// And return true...we tried anyway
		return(true);
	}

	/**
	 * MySQL extension handler for checking a database connection.
	 *
	 * @param n2f_database $db	Current n2f_database object calling the handler
	 * @return boolean			Boolean TRUE or FALSE based on connection's status.
	 */
	function mysql_checkConnection(n2f_database $db) {
		// If it's closed, return false
		if ($db->conn === false) {
			return(false);
		}

		// Obviously we return true otherwise
		return(true);
	}

	/**
	 * MySQL extension handler for adding a query parameter.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling the handler
	 * @param string $key				Key name for parameter
	 * @param mixed $value				Value for parameter
	 * @param integer $type				Type indicator for parameter
	 * @return boolean					Boolean TRUE or FALSE based on action's success.
	 */
	function mysql_addParameter(n2f_database_query &$query, $key, $value, $type) {
		// If it isn't a valid parameter type, return failure
		if ($query->validParamType($type) === false) {
			return(false);
		}

		// If it's anything but binary/raw-string/string, clean it and possibly add quotes around it
		if ($type != N2F_DBTYPE_BINARY && $type != N2F_DBTYPE_RAW_STRING && $type != N2F_DBTYPE_LIKE_STRING) {
			$value = ($type == N2F_DBTYPE_STRING) ? "'" . mysql_cleanParam($value) . "'" : mysql_cleanParam($value);
		} else { // Otherwise, add quotes around it without anything more
			$value = "'{$value}'";
		}

		// Add parameter to the internal list
		$query->params[] = array(
			'key'	=> $key,
			'value'	=> $value,
			'type'	=> $type
		);

		// It's the...eye of the tiger, it's returning false
		return(true);
	}

	/**
	 * MySQL extension handler for executing a query.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling the handler
	 * @return boolean					Boolean TRUE or FALSE based on action's success.
	 */
	function mysql_executeQuery(n2f_database_query &$query) {
		// Grab instance of n2f_cls
		$n2f = n2f_cls::getInstance();

		// If the query appears to be empty, that's bad
		if (strlen($query->query) < 1) {
			// If we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLDB_ERROR_FAILED_QUERY, S('MYSQLDB_ERROR_FAILED_QUERY', array('No query string was provided')), 'n2f_database/mysql.ext.php');
			}

			// Return false to indicate failure
			return(false);
		}

		// If there are parameters supplied to the query object
		if (count($query->params) > 0) {
			$pos = 0;

			// Loop through all of the supplied parameters
			foreach (array_values($query->params) as $param) {
				// Get the position of the next ? in the query (based off of previous last-character to avoid bad replacements)
				$pos = strpos($query->query, '?', $pos);

				// If it's found
				if ($pos !== false) {
					// Grab the query around the ?, do some counting, and add the value
					$before = substr($query->query, 0, $pos);
					$after = substr($query->query, ($pos + 1));
					$newFront = $before . $param['value'];
					$query->query = $newFront . $after;
					$pos = strlen($newFront);
				} else { // Otherwise, we didn't find anymore ?'s, so we might as well bail out
					break;
				}
			}
		}

		// Execute the query silently
		$res = @mysql_query($query->query);

		// If the query was a success, add it to our internal data storage
		if ($res) {
			$query->addData('res', $res);
		} else { // Otherwise, we made a booboo
			// If we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLDB_ERROR_FAILED_QUERY, S('MYSQLDB_ERROR_FAILED_QUERY', array(mysql_error($query->db->conn))), 'n2f_database/mysql.ext.php');
			}

			// Add the error to our little stack/list thinger
			$query->addError(S('MYSQLDB_ERROR_FAILED_QUERY', array(mysql_error($query->db->conn))));

			// Return false to indicate failure
			return(false);
		}

		// Return true to indicate success
		return(true);
	}

	/**
	 * MySQL extension handler for retrieving one row.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @return mixed					Mixed value of next row in result set.
	 */
	function mysql_getRow(n2f_database_query &$query) {
		// Grab instance of n2f_cls, initialize return variable
		$n2f = n2f_cls::getInstance();
		$ret = false;

		// Grab the result set from internal storage
		$res = $query->getData('res');

		// If we found -something- within internal storage
		if ($res !== null) {
			// If it's a success
			if ($res) {
				// And there were rows returned, assign next row to $ret
				if (@mysql_num_rows($res) > 0) {
					$ret = @mysql_fetch_assoc($res);
				}
			} else { // Otherwise it failed
				// If we're throwing errors, throw the error
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(MYSQLDB_ERROR_FAILED_QUERY, S('MYSQLDB_ERROR_FAILED_QUERY', array('Invalid query for operation')), 'n2f_database/mysql.ext.php');
				}
			}
		} else { // We found nothing, big problem
			// If we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLDB_ERROR_NO_QUERY, S('MYSQLDB_ERROR_NO_QUERY'), 'n2f_database/mysql.ext.php');
			}
		}

		// Send back our return value, whatever it may be at this point
		return($ret);
	}

	/**
	 * MySQL extension handler for retrieving all rows from a query.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @return array					Array of rows returned by result set.
	 */
	function mysql_getRows(n2f_database_query &$query) {
		// Grab instance of n2f_cls, initialize return array
		$n2f = n2f_cls::getInstance();
		$ret = array();

		// Grab the result set from internal storage
		$res = $query->getData('res');

		// If we found something
		if ($res !== null) {
			// And it was a success
			if ($res) {
				// And we have rows returned
				if (@mysql_num_rows($res) > 0) {
					// Loop through all and insert into return array, then erase the last one, which will be blank
					while ($ret[] = @mysql_fetch_assoc($res)) { }
					unset($ret[count($ret) - 1]);
				}
			} else { // Failure
				// If we're throwing errors, throw the error
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(MYSQLDB_ERROR_FAILED_QUERY, S('MYSQLDB_ERROR_FAILED_QUERY', array('Invalid query for operation')), 'n2f_database/mysql.ext.php');
				}
			}
		} else { // We found nothing, woops
			// If we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLDB_ERROR_NO_QUERY, S('MYSQLDB_ERROR_NO_QUERY'), 'n2f_database/mysql.ext.php');
			}
		}

		// Return the array, empty or otherwise
		return($ret);
	}

	/**
	 * MySQL extension handler for retrieving the last AUTO_INCREMENT integer from a query, when available.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @return mixed					Mixed value of last auto_increment in table.
	 */
	function mysql_getLastInc(n2f_database_query &$query) {
		// Grab instance of n2f_cls, initialize return variable
		$n2f = n2f_cls::getInstance();
		$ret = null;

		// Grab result set from internal storage
		$res = $query->getData('res');

		// If we found something
		if ($res !== null) {
			// And it's a success, run the function
			if ($res) {
				$ret = @mysql_insert_id();
			} else { // Otherwise, we have a bad query
				// If we're throwing errors, throw the error
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(MYSQLDB_ERROR_FAILED_QUERY, S('MYSQLDB_ERROR_FAILED_QUERY', array('Invalid query for operation')), 'n2f_database/mysql.ext.php');
				}
			}
		} else { // We found nothing, wuh oh
			// If we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLDB_ERROR_NO_QUERY, S('MYSQLDB_ERROR_NO_QUERY'), 'n2f_database/mysql.ext.php');
			}
		}

		// Return the variable, hopefully it has been changed
		return($ret);
	}

	/**
	 * MySQL extension handler for retrieving the number of rows returned by a query, if any.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @return integer					Integer value of the number of rows in the result set.
	 */
	function mysql_getNumRows(n2f_database_query &$query) {
		// Grab instance of n2f_cls, initialize return variable
		$n2f = n2f_cls::getInstance();
		$ret = 0;

		// Grab result set from internal storage
		$res = $query->getData('res');

		// If we found something
		if ($res !== null) {
			// And it's a success, set return to the number of rows
			if ($res) {
				$ret = @mysql_num_rows($res);
			} else { // Otherwise, we're a bad RS
				// If we're throwing errors, throw the error
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(MYSQLDB_ERROR_FAILED_QUERY, S('MYSQLDB_ERROR_FAILED_QUERY', array('Invalid query for operation')), 'n2f_database/mysql.ext.php');
				}
			}
		} else { // We found nothing, serious problem
			// If we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLDB_ERROR_NO_QUERY, S('MYSQLDB_ERROR_NO_QUERY'), 'n2f_database/mysql.ext.php');
			}
		}

		// Return whatever our variable is set as
		return($ret);
	}

	/**
	 * MySQL extension handler for retrieving a specific field from a specific row on a query.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @param integer $offset			Row offset to pull field from
	 * @param string $field_name			Name of field to pull data from
	 * @return mixed					Mixed value of result field.
	 */
	function mysql_getResult(n2f_database_query &$query, $offset, $field_name) {
		// Grab instance of n2f_cls, initialize return variable
		$n2f = n2f_cls::getInstance();
		$ret = null;

		// Grab result set from internal storage
		$res = $query->getData('res');

		// If we found something
		if ($res !== null) {
			// And it's a success
			if ($res) {
				// Check if we have rows, and if we do try grabbing the result
				if (@mysql_num_rows($res) > 0) {
					$ret = @mysql_result($res, $offset, $field_name);
				}
			} else { // Otherwise we had problems
				// If we're throwing errors, throw the error
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(MYSQLDB_ERROR_FAILED_QUERY, S('MYSQLDB_ERROR_FAILED_QUERY', array('Invalid query for operation')), 'n2f_database/mysql.ext.php');
				}
			}
		} else { // We found nothing
			// If we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLDB_ERROR_NO_QUERY, S('MYSQLDB_ERROR_NO_QUERY'), 'n2f_database/mysql.ext.php');
			}
		}

		return($ret);
	}

	/**
	 * Returns a sanitized parameter for MySQL.
	 *
	 * @param mixed $value	Value to sanitize
	 * @return mixed		Returns a theoretically clean value.
	 */
	function mysql_cleanParam($value) {
		// Do some basic escaping here...
		$value = mysql_real_escape_string($value);
		$value = addcslashes($value, "\x00\n\r\'\x1a\x3c\x3e\x25");

		// Return the escaped value
		return($value);
	}

?>