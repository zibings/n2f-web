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
	 * $Id: pgsql.ext.php 192 2012-01-19 20:01:11Z amale@EPSILON $
	 */

	// Global variable(s)
	$n2f = n2f_cls::getInstance();

	// Error constants
	define('PGSQLDB_ERROR_INVALID_CONFIG',				'0001');
	define('PGSQLDB_ERROR_FAILED_DBCONNECT',			'0002');
	define('PGSQLDB_ERROR_FAILED_QUERY',				'0003');
	define('PGSQLDB_ERROR_NO_QUERY',					'0004');
	define('PGSQLDB_ERROR_INVALID_RESULT',				'0005');
	define('PGSQLDB_ERROR_MISSING_PGSQL',				'0006');
	define('PGSQLDB_ERROR_FAILED_PARAMETER',			'0007');

	// English error strings
	L('en', 'PGSQLDB_ERROR_INVALID_CONFIG',				'The configuration submitted for the PgSQL database extension was invalid.');
	L('en', 'PGSQLDB_ERROR_FAILED_DBCONNECT',			'The PgSQL database extension was unable to connect for the following reason(s): _%1%_');
	L('en', 'PGSQLDB_ERROR_FAILED_QUERY',				'Failed to execute a query. (ERROR: _%1%_) (QUERY: _%2%_)');
	L('en', 'PGSQLDB_ERROR_NO_QUERY',					'The action could not be performed because no active query was found.');
	L('en', 'PGSQLDB_ERROR_INVALID_RESULT',				'The result resource referenced was invalid.');
	L('en', 'PGSQLDB_ERROR_MISSING_PGSQL',				'The PHP PgSQL extension is not installed on this system, aborting PgSQL extension registration.');
	L('en', 'PGSQLDB_ERROR_FAILED_PARAMETER',			'A parameter failed to load for the following reason(s): _%1%_');

	// German error strings
	L('de', 'PGSQLDB_ERROR_INVALID_CONFIG',				'The configuration submitted for the PgSQL database extension was invalid.');
	L('de', 'PGSQLDB_ERROR_FAILED_DBCONNECT',			'The PgSQL database extension was unable to connect for the following reason(s): _%1%_');
	L('de', 'PGSQLDB_ERROR_FAILED_QUERY',				'Failed to execute a query. (ERROR: _%1%_) (QUERY: _%2%_)');
	L('de', 'PGSQLDB_ERROR_NO_QUERY',					'The action could not be performed because no active query was found.');
	L('de', 'PGSQLDB_ERROR_INVALID_RESULT',				'The result resource referenced was invalid.');
	L('de', 'PGSQLDB_ERROR_MISSING_PGSQL',				'The PHP PgSQL extension is not installed on this system, aborting PgSQL extension registration.');
	L('de', 'PGSQLDB_ERROR_FAILED_PARAMETER',			'A parameter failed to load for the following reason(s): _%1%_');

	// Spanish error strings
	L('es', 'PGSQLDB_ERROR_INVALID_CONFIG',				'The configuration submitted for the PgSQL database extension was invalid.');
	L('es', 'PGSQLDB_ERROR_FAILED_DBCONNECT',			'The PgSQL database extension was unable to connect for the following reason(s): _%1%_');
	L('es', 'PGSQLDB_ERROR_FAILED_QUERY',				'Failed to execute a query. (ERROR: _%1%_) (QUERY: _%2%_)');
	L('es', 'PGSQLDB_ERROR_NO_QUERY',					'The action could not be performed because no active query was found.');
	L('es', 'PGSQLDB_ERROR_INVALID_RESULT',				'The result resource referenced was invalid.');
	L('es', 'PGSQLDB_ERROR_MISSING_PGSQL',				'The PHP PgSQL extension is not installed on this system, aborting PgSQL extension registration.');
	L('es', 'PGSQLDB_ERROR_FAILED_PARAMETER',			'A parameter failed to load for the following reason(s): _%1%_');

	// Swedish error strings
	L('se', 'PGSQLDB_ERROR_INVALID_CONFIG',				'The configuration submitted for the PgSQL database extension was invalid.');
	L('se', 'PGSQLDB_ERROR_FAILED_DBCONNECT',			'The PgSQL database extension was unable to connect for the following reason(s): _%1%_');
	L('se', 'PGSQLDB_ERROR_FAILED_QUERY',				'Failed to execute a query. (ERROR: _%1%_) (QUERY: _%2%_)');
	L('se', 'PGSQLDB_ERROR_NO_QUERY',					'The action could not be performed because no active query was found.');
	L('se', 'PGSQLDB_ERROR_INVALID_RESULT',				'The result resource referenced was invalid.');
	L('se', 'PGSQLDB_ERROR_MISSING_PGSQL',				'The PHP PgSQL extension is not installed on this system, aborting PgSQL extension registration.');
	L('se', 'PGSQLDB_ERROR_FAILED_PARAMETER',			'A parameter failed to load for the following reason(s): _%1%_');

	// Array of handlers
	$handlers = array(
		N2F_DBEVT_OPEN_CONNECTION					=> 'pgsql_openConnection',
		N2F_DBEVT_CLOSE_CONNECTION					=> 'pgsql_closeConnection',
		N2F_DBEVT_CHECK_CONNECTION					=> 'pgsql_checkConnection',
		N2F_DBEVT_ADD_PARAMETER						=> 'pgsql_addParameter',
		N2F_DBEVT_EXECUTE_QUERY						=> 'pgsql_executeQuery',
		N2F_DBEVT_GET_ROW							=> 'pgsql_getRow',
		N2F_DBEVT_GET_ROWS							=> 'pgsql_getRows',
		N2F_DBEVT_GET_LAST_INC						=> 'pgsql_getLastInc',
		N2F_DBEVT_GET_NUMROWS						=> 'pgsql_getNumRows',
		N2F_DBEVT_GET_RESULT						=> 'pgsql_getResult'
	);

	// Check that the PostgreSQL library is available
	if (function_exists('pg_connect')) {
		n2f_database::addExtension('pgsql', $handlers);

		$n2f->registerExtension(
			'n2f_database/pgsql',
			'n2f_pgsql_database',
			0.1,
			'Andrew Male',
			'http://n2framework.com/'
		);
	} else {
		if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
			$n2f->debug->throwError(PGSQLDB_ERROR_MISSING_PGSQL, S('PGSQLDB_ERROR_MISSING_PGSQL'), 'n2f_database/pgsql.ext.php');
		}
	}

	/**
	 * Function to attempt opening a connection for a PgSQL server.
	 *
	 * @param n2f_database $db	n2f_database object which is calling for the connection.
	 * @param n2f_cfg_db $cfg	n2f_cfg_db object which includes the configuration information for the connection.
	 * @return boolean
	 */
	function pgsql_openConnection(n2f_database &$db, n2f_cfg_db $cfg) {
		$n2f = n2f_cls::getInstance();

		if (!defined('PGSQLDB_CAN_CACHE')) {
			define('PGSQLDB_CAN_CACHE',		(bool)$n2f->hasExtension('n2f_cache'));
		}

		if (!isset($cfg->host) || !isset($cfg->name) || !isset($cfg->user) || !isset($cfg->pass)) {
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(PGSQLDB_ERROR_INVALID_CONFIG, S('PGSQLDB_ERROR_INVALID_CONFIG'), 'n2f_database/pgsql.ext.php');
			}

			$db->conn = false;

			return(false);
		} else {
			$conn_str = "";
			$conn_str .= "host='{$cfg->host}'";
			$conn_str .= "dbname='{$cfg->name}'";
			$conn_str .= "user='{$cfg->user}'";
			$conn_str .= "password='{$cfg->pass}'";

			$db->conn = @pg_connect($conn_str, true);

			if (!$db->isOpen()) {
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(PGSQLDB_ERROR_FAILED_DBCONNECT, S('PGSQLDB_ERROR_FAILED_DBCONNECT', array(pg_last_error($db->conn))), 'n2f_database/pgsql.ext.php');
				}

				$db->conn = false;

				return(false);
			}
		}

		return(true);
	}

	/**
	 * Function to attempt closing a connection for a PgSQL server.
	 *
	 * @param n2f_database $db	n2f_database object closing the connection.
	 * @return boolean
	 */
	function pgsql_closeConnection(n2f_database &$db) {
		if ($db->isOpen() === false) {
			return(false);
		}

		@pg_close($db->conn);

		return(true);
	}

	/**
	 * Function to check if a PgSQL server connection is active.
	 *
	 * @param n2f_database $db	n2f_database object checking the connection state.
	 * @return boolean
	 */
	function pgsql_checkConnection(n2f_database $db) {
		if ($db->conn == false) {
			return(false);
		}

		return(true);
	}

	/**
	 * Function to add a parameter to the query object.
	 *
	 * @param n2f_database_query $query	n2f_database_query object to add the parameter to.
	 * @param string $key				String value naming the parameter.
	 * @param mixed $value				Mixed value of the parameter.
	 * @param string $type				String value (only null available) of the parameter's type.
	 * @return boolean
	 */
	function pgsql_addParameter(n2f_database_query &$query, $key, $value, $type) {
		// No types for this bugger
		$type = null;

		$query->params[] = array(
			'key'	=> $key,
			'value'	=> $value,
			'type'	=> $type
		);

		return(true);
	}

	/**
	 * Function to execute a PgSQL query on the server.
	 *
	 * @param n2f_database_query $query	n2f_database_query object being executed.
	 * @return boolean
	 */
	function pgsql_executeQuery(n2f_database_query &$query) {
		$n2f = n2f_cls::getInstance();

		if (strlen($query->query) < 1) {
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(PGSQLDB_ERROR_FAILED_QUERY, S('PGSQLDB_ERROR_FAILED_QUERY', array('Invalid query string supplied.', '')), 'n2f_database/pgsql.ext.php');
			}

			return(false);
		}

		pg_set_error_verbosity(PGSQL_ERRORS_VERBOSE);

		if (count($query->params) > 0) {
			$params = array();

			foreach (array_values($query->params) as $param) {
				$params[] = $param['value'];
			}

			$res = @pg_query_params($query->db->conn, $query->query, $params);
		} else {
			$res = @pg_query($query->db->conn, $query->query);
		}

		if ($res === false) {
			$error = pg_last_error();

			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(PGSQLDB_ERROR_FAILED_QUERY, S('PGSQLDB_ERROR_FAILED_QUERY', array($error, $query->query)), 'n2f_database/pgsql.ext.php');
			}

			$query->addError(S('PGSQLDB_ERROR_FAILED_QUERY', array($error, $query->query)));

			return(false);
		}

		$query->addData('res', $res);

		return(true);
	}

	/**
	 * Function to get a single row from a PgSQL query result.
	 *
	 * @param n2f_database_query $query	n2f_database_query object to retrieve result and row from.
	 * @return mixed
	 */
	function pgsql_getRow(n2f_database_query &$query) {
		$n2f = n2f_cls::getInstance();
		$ret = false;

		$res = $query->getData('res');

		if ($res !== null) {
			if ($res !== false) {
				if (@pg_num_rows($res) > 0) {
					$ret = @pg_fetch_assoc($res);
				} else {
					if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
						$n2f->debug->throwError(PGSQLDB_ERROR_FAILED_QUERY, S('PGSQLDB_ERROR_FAILED_QUERY', array('No rows returned by query.', $query->query)), 'n2f_database/pgsql.ext.php');
					}
				}
			} else {
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(PGSQLDB_ERROR_NO_QUERY, S('PGSQLDB_ERROR_NO_QUERY'), 'n2f_database/pgsql.ext.php');
				}
			}
		}

		return($ret);
	}

	/**
	 * Function to get all rows in a PgSQL query result set.
	 *
	 * @param n2f_database_query $query	n2f_database_query object to retrieve rows from.
	 * @return mixed
	 */
	function pgsql_getRows(n2f_database_query &$query) {
		$n2f = n2f_cls::getInstance();
		$ret = false;

		$res = $query->getData('res');

		if ($res !== null) {
			if ($res !== false) {
				if (@pg_num_rows($res) > 0) {
					$ret = array();

					while ($row = @pg_fetch_assoc($res)) {
						$ret[] = $row;
					}
				} else {
					if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
						$n2f->debug->throwError(PGSQLDB_ERROR_FAILED_QUERY, S('PGSQLDB_ERROR_FAILED_QUERY', array('No rows returned by query.', $query->query)), 'n2f_database/pgsql.ext.php');
					}
				}
			} else {
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(PGSQLDB_ERROR_NO_QUERY, S('PGSQLDB_ERROR_NO_QUERY'), 'n2f_database/pgsql.ext.php');
				}
			}
		}

		return($ret);
	}

	/**
	 * Function to return the last incremented identifier from a PgSQL query result.
	 *
	 * @param n2f_database_query $query	n2f_database_query object to get the last incremented value from.
	 * @return mixed
	 */
	function pgsql_getLastInc(n2f_database_query &$query) {
		$n2f = n2f_cls::getInstance();
		$ret = null;

		/*
		 * This function is currently incomplete until we can figure out
		 * a reliable method for dynamically getting the sequence ID from
		 * Postgre to build the SELECT currval() query needed for this
		 * functionality.
		 */

		$query->addError("Last incremented functionality not complete.");

		if ($n2f->debug->showLevel(N2F_DEBUG_WARN)) {
			$n2f->debug->throwWarning('----', "Last incremented functionality not complete.", 'n2f_database/pgsql.ext.php');
		}

		return($ret);
	}

	/**
	 * Function to return the number of rows returned in a PgSQL query result.
	 *
	 * @param n2f_database_query $query	n2f_database_query object to get number of rows from.
	 * @return integer
	 */
	function pgsql_getNumRows(n2f_database_query &$query) {
		$n2f = n2f_cls::getInstance();
		$ret = 0;

		$res = $query->getData('res');

		if ($res !== null) {
			if ($res !== false) {
				$ret = @pg_num_rows($res);
			} else {
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(PGSQLDB_ERROR_INVALID_RESULT, S('PGSQLDB_ERROR_INVALID_RESULT'), 'n2f_database/pgsql.ext.php');
				}
			}
		} else {
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(PGSQLDB_ERROR_NO_QUERY, S('PGSQLDB_ERROR_NO_QUERY'), 'n2f_database/pgsql.ext.php');
			}
		}

		return($ret);
	}

	/**
	 * Function to return a single result column from a PgSQL query.
	 *
	 * @param n2f_database_query $query	n2f_database_query object to get result column from.
	 * @param integer $offset			Integer value determining the row offset to get column from.
	 * @param string $field_name			String value of column name to get.
	 * @return mixed
	 */
	function pgsql_getResult(n2f_database_query &$query, $offset, $field_name) {
		$n2f = n2f_cls::getInstance();
		$ret = null;

		$res = $query->getData('res');

		if ($res !== null) {
			if ($res !== false) {
				$ret = @pg_fetch_result($res, $offset, '"'.$field_name.'"');
				$ret = ($ret == 't' || $ret == 'f') ? (($ret == 't') ? true : false) : $ret;
			} else {
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(PGSQLDB_ERROR_INVALID_RESULT, S('PGSQLDB_ERROR_INVALID_RESULT'), 'n2f_database/pgsql.ext.php');
				}
			}
		} else {
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(PGSQLDB_ERROR_NO_QUERY, S('PGSQLDB_ERROR_NO_QUERY'), 'n2f_database/pgsql.ext.php');
			}
		}

		return($ret);
	}

?>