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
	 * $Id: mysqli.ext.php 198 2012-03-28 12:03:02Z amale@EPSILON $
	 */

	// Global variable(s)
	$n2f = n2f_cls::getInstance();

	// MySQLi option constants
	define('MYSQLIDB_OPTION_AUTOCOMMIT',					1);
	define('MYSQLIDB_OPTION_PREPARED',						2);

	// Error constants
	define('MYSQLIDB_ERROR_INVALID_CONFIG',					'0001');
	define('MYSQLIDB_ERROR_FAILED_CONNECT',					'0002');
	define('MYSQLIDB_ERROR_FAILED_PARAMETER',				'0003');
	define('MYSQLIDB_ERROR_FAILED_QUERY_PREPARE',			'0004');
	define('MYSQLIDB_ERROR_OPEN_RESULT',					'0005');
	define('MYSQLIDB_ERROR_FAILED_QUERY',					'0006');
	define('MYSQLIDB_ERROR_NO_RESULT',						'0007');
	define('MYSQLIDB_ERROR_MISSING_MYSQLI',					'0008');

	// English error strings
	L('en', 'MYSQLIDB_ERROR_INVALID_CONFIG',				'The configuration submitted for the MySQLi database extension was invalid.');
	L('en', 'MYSQLIDB_ERROR_FAILED_CONNECT',				'Failed to connect to the MySQL database. (_%1%_)');
	L('en', 'MYSQLIDB_ERROR_FAILED_PARAMETER',				'A parameter failed to load for the following reason(s): _%1%_');
	L('en', 'MYSQLIDB_ERROR_FAILED_QUERY_PREPARE',			'Failed to prepare the query for execution.');
	L('en', 'MYSQLIDB_ERROR_OPEN_RESULT',					'Unable to execute query, another result is currently open or has failed.');
	L('en', 'MYSQLIDB_ERROR_FAILED_QUERY',					'Failed to execute the query.');
	L('en', 'MYSQLIDB_ERROR_NO_RESULT',					'No result was available or open.');
	L('en', 'MYSQLIDB_ERROR_MISSING_MYSQLI',				'The PHP MySQLi extension is not installed on this system, aborting MySQLi extension registration.');

	// German error strings
	L('de', 'MYSQLIDB_ERROR_INVALID_CONFIG',				'The configuration submitted for the MySQLi database extension was invalid.');
	L('de', 'MYSQLIDB_ERROR_FAILED_CONNECT',				'Failed to connect to the MySQL database. (_%1%_)');
	L('de', 'MYSQLIDB_ERROR_FAILED_PARAMETER',				'A parameter failed to load for the following reason(s): _%1%_');
	L('de', 'MYSQLIDB_ERROR_FAILED_QUERY_PREPARE',			'Failed to prepare the query for execution.');
	L('de', 'MYSQLIDB_ERROR_OPEN_RESULT',					'Unable to execute query, another result is currently open or has failed.');
	L('de', 'MYSQLIDB_ERROR_FAILED_QUERY',					'Failed to execute the query.');
	L('de', 'MYSQLIDB_ERROR_NO_RESULT',					'No result was available or open.');
	L('de', 'MYSQLIDB_ERROR_MISSING_MYSQLI',				'The PHP MySQLi extension is not installed on this system, aborting MySQLi extension registration.');

	// Spanish error strings
	L('es', 'MYSQLIDB_ERROR_INVALID_CONFIG',				'The configuration submitted for the MySQLi database extension was invalid.');
	L('es', 'MYSQLIDB_ERROR_FAILED_CONNECT',				'Failed to connect to the MySQL database. (_%1%_)');
	L('es', 'MYSQLIDB_ERROR_FAILED_PARAMETER',				'A parameter failed to load for the following reason(s): _%1%_');
	L('es', 'MYSQLIDB_ERROR_FAILED_QUERY_PREPARE',			'Failed to prepare the query for execution.');
	L('es', 'MYSQLIDB_ERROR_OPEN_RESULT',					'Unable to execute query, another result is currently open or has failed.');
	L('es', 'MYSQLIDB_ERROR_FAILED_QUERY',					'Failed to execute the query.');
	L('es', 'MYSQLIDB_ERROR_NO_RESULT',					'No result was available or open.');
	L('es', 'MYSQLIDB_ERROR_MISSING_MYSQLI',				'The PHP MySQLi extension is not installed on this system, aborting MySQLi extension registration.');

	// Swedish error strings
	L('se', 'MYSQLIDB_ERROR_INVALID_CONFIG',				'The configuration submitted for the MySQLi database extension was invalid.');
	L('se', 'MYSQLIDB_ERROR_FAILED_CONNECT',				'Failed to connect to the MySQL database. (_%1%_)');
	L('se', 'MYSQLIDB_ERROR_FAILED_PARAMETER',				'A parameter failed to load for the following reason(s): _%1%_');
	L('se', 'MYSQLIDB_ERROR_FAILED_QUERY_PREPARE',			'Failed to prepare the query for execution.');
	L('se', 'MYSQLIDB_ERROR_OPEN_RESULT',					'Unable to execute query, another result is currently open or has failed.');
	L('se', 'MYSQLIDB_ERROR_FAILED_QUERY',					'Failed to execute the query.');
	L('se', 'MYSQLIDB_ERROR_NO_RESULT',					'No result was available or open.');
	L('se', 'MYSQLIDB_ERROR_MISSING_MYSQLI',				'The PHP MySQLi extension is not installed on this system, aborting MySQLi extension registration.');

	// Create our array of handlers
	$handlers = array(
		N2F_DBEVT_OPEN_CONNECTION						=> 'mysqli_openConnection',
		N2F_DBEVT_CLOSE_CONNECTION						=> 'mysqli_closeConnection',
		N2F_DBEVT_CHECK_CONNECTION						=> 'mysqli_checkConnection',
		N2F_DBEVT_ADD_PARAMETER							=> 'mysqli_addParameter',
		N2F_DBEVT_EXECUTE_QUERY							=> 'mysqli_executeQuery',
		N2F_DBEVT_GET_ROW								=> 'mysqli_getRow',
		N2F_DBEVT_GET_ROWS								=> 'mysqli_getRows',
		N2F_DBEVT_GET_LAST_INC							=> 'mysqli_getLastInc',
		N2F_DBEVT_GET_NUMROWS							=> 'mysqli_getNumRows',
		N2F_DBEVT_GET_RESULT							=> 'mysqli_getResult'
	);

	// If it looks like the mysqli extension is available
	if (function_exists('mysqli_autocommit')) {
		// Register the callbacks
		n2f_database::addExtension('mysqli', $handlers);

		// And then register the extension's meta data
		$n2f->registerExtension(
			'n2f_database/mysqli',
			'n2f_mysqli_database',
			'0.2.1',
			'Andrew Male',
			'http://n2framework.com/'
		);
	} else { // Otherwise, we can't do much here
		// If we're throwing errors, throw the error
		if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
			$n2f->debug->throwError(MYSQLIDB_ERROR_MISSING_MYSQLI, S('MYSQLIDB_ERROR_MISSING_MYSQLI'), 'mysqli.ext.php');
		}
	}


	/**
	 * MySQLi extension handler for opening a database connection.
	 *
	 * @param n2f_database $db	Current n2f_database object calling the handler
	 * @param n2f_cfg_db $cfg	Configuration value from n2f_cls::cfg for n2f_database object
	 * @return boolean			Boolean value indicating operation's success.
	 */
	function mysqli_openConnection(n2f_database &$db, n2f_cfg_db $cfg) {
		// Grab global instance
		$n2f = n2f_cls::getInstance();

		// If we haven't already, define our 'can_cache' check
		if (!defined('MYSQLIDB_CAN_CACHE')) {
			define('MYSQLIDB_CAN_CACHE',		(bool)$n2f->hasExtension('n2f_cache'));
		}

		// If we're missing any of our important ones...
		if (!isset($cfg->host) || !isset($cfg->user) || !isset($cfg->pass) || !isset($cfg->name)) {
			// And we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLIDB_ERROR_INVALID_CONFIG, S('MYSQLIDB_ERROR_INVALID_CONFIG'), 'mysqli.ext.php');
			}

			// Just to be sure we are clear with this one
			$db->conn = false;

			// And go softly into the night
			return(false);
		} else { // Otherwise, we may proceed
			// If we have -all- of them, do that
			if ($cfg->port !== null && $cfg->sock !== null) {
				$db->conn = @mysqli_connect($cfg->host, $cfg->user, $cfg->pass, $cfg->name, $cfg->port, $cfg->sock);
			} else if ($cfg->port !== null) { // Or if we just have the optional port, pass that along
				$db->conn = @mysqli_connect($cfg->host, $cfg->user, $cfg->pass, $cfg->name, $cfg->port);
			} else { // Or hell, just do it the normal way
				$db->conn = @mysqli_connect($cfg->host, $cfg->user, $cfg->pass, $cfg->name);
			}

			// Did we find an error?
			if (mysqli_connect_error() != '') {
				// If we're throwing errors, throw the error
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(MYSQLIDB_ERROR_FAILED_CONNECT, S('MYSQLIDB_ERROR_FAILED_CONNECT', array(mysqli_connect_error())), 'mysqli.ext.php');
				}

				// Again, just being clear
				$db->conn = false;

				// And failing
				return(false);
			}
		}

		// Now that we're golden, we'll connect the extra callback we need to check for options
		$db->hookEvent(N2F_DBEVT_QUERY_CREATED, 'mysqli_queryCreated');

		// Winning
		return(true);
	}

	/**
	 * MySQLi extension handler for query creation.
	 *
	 * @param n2f_database_query $query	Newly created n2f_database_query object.
	 * @param n2f_database $db			Current n2f_database object calling the handler.
	 */
	function mysqli_queryCreated(n2f_database_query &$query, n2f_database &$db) {
		// If we have an open connection
		if ($db->isOpen()) {
			// Grab any options that the query was given
			$opts = $query->getData('options');

			// If we had options, and one of them included the OPTION_PREPARED doohickey
			if ($opts !== null && $opts & MYSQLIDB_OPTION_PREPARED) {
				// Generate a (probably) unique name for the query, store it in the query's data stack
				$qName = md5(time());
				$query->addData('qName', $qName);

				// And then run the query to tell MySQL we're doing this thing
				$db->conn->real_query('PREPARE '.$qName.' FROM \''.$query->query.'\'');
			}
		}
	}

	/**
	 * MySQLi extension handler for closing a database connection.
	 *
	 * @param n2f_database $db	Current n2f_database object calling the handler.
	 * @return boolean			Boolean value indicating operation's success.
	 */
	function mysqli_closeConnection(n2f_database &$db) {
		// No open connection means we won't succeed
		if ($db->isOpen() === false) {
			return(false);
		}

		// Grab any queries on the books
		$queries = $db->getData('queries');

		// If we have some queries
		if ($queries !== null) {
			// Loop through each query
			foreach (array_values($queries) as $query) {
				// And if it's the right kind, call our closer
				if ($query instanceof mysqli_stmt || $query instanceof mysqli_result) {
					$query->close();
				}
			}
		}

		// Finally, go ahead and close the entire connection
		$db->conn->close();

		// And let the world know we're moving up
		return(true);
	}

	/**
	 * MySQLi extension handler for checking a database connection.
	 *
	 * @param n2f_database $db	Current n2f_database object calling the handler
	 * @return boolean			Boolean value indicating operation's success.
	 */
	function mysqli_checkConnection(n2f_database $db) {
		// If we aren't setup, say so
		if (!isset($db->conn) || $db->conn == false) {
			return(false);
		}

		// I think we're OK here though
		return(true);
	}

	/**
	 * MySQLi extension handler for adding parameters to a prepared statement.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @param string $key				Key name of parameter (not supported)
	 * @param mixed $value				Value of parameter
	 * @param mixed $type				Type of parameter (N2F_DBTYPE_*)
	 * @return boolean					Boolean value indicating operation's success.
	 */
	function mysqli_addParameter(n2f_database_query &$query, $key, $value, $type) {
		// Ahoy matey
		$n2f = n2f_cls::getInstance();

		// Pull in any options
		$opts = $query->getData('options');

		// Provided we have an open connection
		if ($query->db->isOpen()) {
			// If the type aint right
			if ($query->validParamType($type) === false) {
				// If we're throwing errors, throw the error
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(MYSQLIDB_ERROR_FAILED_PARAMETER, S('MYSQLIDB_ERROR_FAILED_PARAMETER', array('Invalid type provided for parameter')), 'mysqli.ext.php');
				}

				// And pass the buck
				return(false);
			} else { // If, however, the narwhal IS baconing (no...not 9gag...never 9gag)
				// Do basic sanitization if required
				$value = ($type != N2F_DBTYPE_BINARY && $type != N2F_DBTYPE_LIKE_STRING && $type != N2F_DBTYPE_RAW_STRING) ? mysqli_sanitizeData($query, $value, $type) : "'{$value}'";

				// Pick out our type
				switch ($type) {
					case N2F_DBTYPE_BINARY:
						$type = 'b';
						break;
					case N2F_DBTYPE_DOUBLE:
						$type = 'd';
						break;
					case N2F_DBTYPE_INTEGER:
						$type = 'i';
						break;
					case N2F_DBTYPE_LIKE_STRING:
					case N2F_DBTYPE_RAW_STRING:
					case N2F_DBTYPE_STRING:
					default:
						$type = 's';
						break;
				}

				// Add to the heap
				$query->params[] = array(
					'key'	=> $key,
					'value'	=> $value,
					'type'	=> $type
				);

				// And if we're doing this as a 'real' prepared statement, pass the param onto the server
				if ($opts !== null && $opts & MYSQLIDB_OPTION_PREPARED && $query->db->isOpen()) {
					$query->db->conn->real_query('SET @v'.(count($query->params) - 1).' = \''.$value.'\'');
				}
			}
		} else { // No point if we aren't even connected
			// If we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLIDB_ERROR_FAILED_PARAMETER, S('MYSQLIDB_ERROR_FAILED_PARAMETER', array('The database connection was closed')), 'mysqli.ext.php');
			}

			// And again, buck passed
			return(false);
		}

		// I guess this is still a passed buck, just a better one?
		return(true);
	}

	/**
	 * MySQLi extension handler for executing the query.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @return boolean					Boolean value indicating operation's success.
	 */
	function mysqli_executeQuery(n2f_database_query &$query) {
		// Global...
		$n2f = n2f_cls::getInstance();

		// Grab any possible result and options
		$result = $query->getData('result');
		$opts = $query->getData('options');

		// If we have a result and we have an error, we have a problem
		if ($result !== null || $query->isError()) {
			// If we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLIDB_ERROR_OPEN_RESULT, S('MYSQLIDB_ERROR_OPEN_RESULT'), 'mysqli.ext.php');
			}

			// Toss an error into the query heap
			$query->addError(S('MYSQLIDB_ERROR_OPEN_RESULT'));

			// And back off
			return(false);
		}

		// If we have parameters and need them
		if (count($query->params) > 0 && strpos($query->query, '?') !== false) {
			// True prepared statement?
			if ($opts !== null && $opts & MYSQLIDB_OPTION_PREPARED) {
				// Grab the query name and start our variable array
				$qName = $query->getData('qName');
				$vars = array();

				// Loop through all of our parameters and add them by name/number
				foreach (array_keys($query->params) as $offset) {
					$vars[] = '@v'.$offset;
				}

				// Execute the query this way
				$result = $query->db->conn->query('EXECUTE '.$qName.' USING '.implode(', ', array_values($vars)));
			} else { // Otherwise, go the normal route
				// Index holder (so we don't overwrite question marks inside values
				$pos = 0;

				// Loop through all parameters
				foreach (array_values($query->params) as $param) {
					// Look for our marker
					$pos = strpos($query->query, '?', $pos);

					// Found it?
					if ($pos !== false) {
						// Grab everything before and after, add to 'before', get the new end-position index and continue
						$before = substr($query->query, 0, $pos);
						$after = substr($query->query, ($pos + 1));
						$newFront = $before . $param['value'];
						$query->query = $newFront . $after;
						$pos = strlen($newFront);
					} else { // Well if it's not there, might as well give up
						break;
					}
				}

				// And then we execute this way
				$result = $query->db->conn->query($query->query);
			}
		} else { // No params, simple execution
			$result = $query->db->conn->query($query->query);
		}

		// If the query execution was useful
		if ($result) {
			// Toss the result into the storage bin
			$query->addData('result', $result);

			// Pull out the query array from our db object
			$queries = $query->db->getData('queries');

			// If we already have queries, just toss it on the end
			if ($queries !== null) {
				$queries[] = $result;
			} else { // Otherwise, create the array
				$queries = array($result);
			}

			// Annnnd put it back into storage
			$query->db->addData('queries', $queries);

			// If we're supposed to commit this automatically, do so
			if ($opts !== null && $opts & MYSQLIDB_OPTION_AUTOCOMMIT) {
				mysqli_doCommit($query);
			}
		} else { // If we're here, something went wrong
			// If we're throwing errors, throw error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLIDB_ERROR_FAILED_QUERY, S('MYSQLIDB_ERROR_FAILED_QUERY'), 'mysqli.ext.php');
			}

			// Add the basic error and the detailed error in sequence
			$query->addError(S('MYSQLIDB_ERROR_FAILED_QUERY'));
			$query->addError($query->db->conn->error);

			// Go back a failure
			return(false);
		}

		// My hero!
		return(true);
	}

	/**
	 * Static method for turning off MySQL's autocommit feature.
	 *
	 * @param n2f_database_query $query	Query to attempt turning off the autocommit feature for
	 * @return null					Null, not really any reason to return anything.
	 */
	function mysqli_doAutoCommit(n2f_database_query &$query) {
		// If we have an open connection, call autocommit on it
		if ($query->db->isOpen()) {
			$query->db->conn->autocommit(false);
		}

		// Return nothing
		return(null);
	}

	/**
	 * Static method for beginning a transaction on a query.
	 *
	 * @param n2f_database_query $query	Query to attempt starting a transaction on
	 * @return null					Null, not really any reason to return anything.
	 */
	function mysqli_doTranscation(n2f_database_query &$query) {
		// Global...ooo, so bad for you *rolls eyes*
		$n2f = n2f_cls::getInstance();

		// If we have an open connection
		if ($query->db->isOpen()) {
			// Begin transaction
			$result = $query->db->conn->query("START TRANSACTION");

			// If we didn't have a favorable response and we're throwing errors, throw the error
			if (!$result && $n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLIDB_ERROR_FAILED_QUERY, S('MYSQLIDB_ERROR_FAILED_QUERY'), 'mysqli.ext.php');
			}
		}

		// Return nothing
		return(null);
	}

	/**
	 * Static method for committing a transaction on a query.
	 *
	 * @param n2f_database_query $query	Query to attempt commit on
	 * @return null					Null, not really any reason to return anything.
	 */
	function mysqli_doCommit(n2f_database_query &$query) {
		// Global instance
		$n2f = n2f_cls::getInstance();

		// If connection is open
		if ($query->db->isOpen()) {
			// Toss out the commit query
			$result = $query->db->conn->query("COMMIT");

			// If we failed and we're throwing errors, throw the error
			if (!$result && $n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLIDB_ERROR_FAILED_QUERY, S('MYSQLIDB_ERROR_FAILED_QUERY'), 'mysqli.ext.php');
			}
		}

		// Return...nothing?
		return(null);
	}

	/**
	 * Static method for rolling back a transaction on a query.
	 *
	 * @param n2f_database_query $query	Query to attempt rollback on
	 * @return null					Null, not really any reason to return anything.
	 */
	function mysqli_doRollback(n2f_database_query &$query) {
		// Global instance
		$n2f = n2f_cls::getInstance();

		// If connection is open
		if ($query->db->isOpen()) {
			// Throw the rollback command
			$result = $query->db->conn->query("ROLLBACK");

			// If we failed and we're throwing errors, throw the error
			if (!$result && $n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLIDB_ERROR_FAILED_QUERY, S('MYSQLIDB_ERROR_FAILED_QUERY'), 'mysqli.ext.php');
			}
		}

		// Still nadda
		return(null);
	}

	/**
	 * MySQLi extension handler for pulling one row from the query.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @return mixed					Array of row contents or null upon failure.
	 */
	function mysqli_getRow(n2f_database_query &$query) {
		// Global instance
		$n2f = n2f_cls::getInstance();

		// Initialize our return variable
		$ret = null;

		// Grab the result from storage
		$result = $query->getData('result');

		// If we have a result, pull the next associative array
		if ($result !== null) {
			$ret = $result->fetch_assoc();
		} else { // Otherwise, well hard to get rows without a result
			// If we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLIDB_ERROR_NO_RESULT, S('MYSQLIDB_ERROR_NO_RESULT'), 'mysqli.ext.php');
			}
		}

		// Return our ret..urn
		return($ret);
	}

	/**
	 * MySQLi extension handler for pulling all rows from a query.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @return array					Array of rows from result, empty array upon failure or no rows.
	 */
	function mysqli_getRows(n2f_database_query &$query) {
		// Global instance
		$n2f = n2f_cls::getInstance();

		// Initialize our return variable
		$ret = array();

		// Pull result from storage
		$result = $query->getData('result');

		// If result is there, pull all rows and delete extra row on end
		if ($result !== null) {
			while ($ret[] = $result->fetch_assoc()) { }
			unset($ret[count($ret) - 1]);
		} else { // Otherwise, well there aren't any rows
			// If we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLIDB_ERROR_NO_RESULT, S('MYSQLIDB_ERROR_NO_RESULT'), 'mysqli.ext.php');
			}
		}

		// Return our return
		return($ret);
	}

	/**
	 * MySQLi extension handler for getting the last AUTO_INCREMENT value produced by a query's INSERT statement.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @return mixed					Integer value of last increment value or null upon failure.
	 */
	function mysqli_getLastInc(n2f_database_query &$query) {
		// Global instance
		$n2f = n2f_cls::getInstance();

		// Initialize return variable
		$ret = null;

		// Pull result from storage
		$result = $query->getData('result');

		// If we have a result
		if ($result !== null) {
			// Check the id value, if good set return variable as just that
			if ($query->db->conn->insert_id > 0) {
				$ret = $query->db->conn->insert_id;
			}
		} else { // Again, this makes the process difficult
			// If we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLIDB_ERROR_NO_RESULT, S('MYSQLIDB_ERROR_NO_RESULT'), 'mysqli.ext.php');
			}
		}

		// Return whatever we've got
		return($ret);
	}

	/**
	 * MySQLi extension handler for getting the number of rows returned by a result.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @return integer					Integer value representing the number of rows.
	 */
	function mysqli_getNumRows(n2f_database_query &$query) {
		// Global instance
		$n2f = n2f_cls::getInstance();

		// Initialize return
		$ret = 0;

		// Pull result
		$result = $query->getData('result');

		// If resulted, set return to number of rows
		if ($result !== null) {
			$ret = $result->num_rows;
		} else { // Otherwise, error
			// If we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLIDB_ERROR_NO_RESULT, S('MYSQLIDB_ERROR_NO_RESULT'), 'mysqli.ext.php');
			}
		}

		// Return what we've got
		return($ret);
	}

	/**
	 * MySQLi extension handler for getting a field from a query.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @param unknown_type $offset		Row offset to get field value from
	 * @param unknown_type $field_name		Name of field to get value from
	 * @return mixed					Mixed value of field from query row.
	 */
	function mysqli_getResult(n2f_database_query &$query, $offset, $field_name) {
		// Global instance
		$n2f = n2f_cls::getInstance();

		// Initialize return variable
		$ret = null;

		// Pull result from storage
		$result = $query->getData('result');

		// If we have a result, seek to what we want and pull our value
		if ($result !== null) {
			$result->data_seek($offset);
			$row = $result->fetch_assoc();
			$ret = $row[$field_name];
		} else { // Otherwise, big bad booboo
			// If we're throwing errors, throw the error
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(MYSQLIDB_ERROR_NO_RESULT, S('MYSQLIDB_ERROR_NO_RESULT'), 'mysqli.ext.php');
			}
		}

		// Return what we've got
		return($ret);
	}

	/**
	 * MySQLi extension handler for sanitizing data used in a query.
	 *
	 * @param n2f_database_query $query	Query object to associate with sanitization
	 * @param mixed $data				Data to sanitize
	 * @param boolean $type				Whether or not data is being used in a LIKE statement
	 * @return mixed					Mixed value of sanitized data.
	 */
	function mysqli_sanitizeData(n2f_database_query &$query, $data, $type) {
		// If connection is open
		if ($query->db->isOpen()) {
			// Escape that string
			$data = mysqli_real_escape_string($query->db->conn, $data);

			// If it's not an integer, enclose it
			if ($type != N2F_DBTYPE_INTEGER) {
				$data = "'{$data}'";
			}
		}

		// Return what we've got
		return($data);
	}

?>