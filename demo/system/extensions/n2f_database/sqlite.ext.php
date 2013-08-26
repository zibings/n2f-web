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
	 * $Id: sqlite.ext.php 192 2012-01-19 20:01:11Z amale@EPSILON $
	 */

	// Global variable(s)
	$n2f = n2f_cls::getInstance();

	// Error constants
	define('SQLITEDB_ERROR_INVALID_CONFIG',				'0001');
	define('SQLITEDB_ERROR_FAILED_DBACCESS',			'0002');
	define('SQLITEDB_ERROR_FAILED_DBOPEN',				'0003');
	define('SQLITEDB_ERROR_FAILED_QUERY',				'0004');
	define('SQLITEDB_ERROR_NO_QUERY',					'0005');
	define('SQLITEDB_ERROR_MISSING_SQLITE',				'0006');

	// English error strings
	L('en', 'SQLITEDB_ERROR_INVALID_CONFIG',			'The configuration submitted for the SQLite database extension was invalid.');
	L('en', 'SQLITEDB_ERROR_FAILED_DBACCESS',			"The SQLite database extension was unable to access the '_%1%_' database for the following reason(s): _%2%_");
	L('en', 'SQLITEDB_ERROR_FAILED_DBOPEN',				"The SQLite database extension was unable to open for the following reason(s): _%1%_");
	L('en', 'SQLITEDB_ERROR_FAILED_QUERY',				'Failed to execute a query. (_%1%_)');
	L('en', 'SQLITE_ERROR_NO_QUERY',					'Operation performed on a non-query.');
	L('en', 'SQLITEDB_ERROR_MISSING_SQLITE',			'The PHP SQLite extension is not installed on this system, aborting SQLite extension registration.');

	// German error strings
	L('de', 'SQLITEDB_ERROR_INVALID_CONFIG',			'The configuration submitted for the SQLite database extension was invalid.');
	L('de', 'SQLITEDB_ERROR_FAILED_DBACCESS',			"The SQLite database extension was unable to access the '_%1%_' database for the following reason(s): _%2%_");
	L('de', 'SQLITEDB_ERROR_FAILED_DBOPEN',				"The SQLite database extension was unable to open for the following reason(s): _%1%_");
	L('de', 'SQLITEDB_ERROR_FAILED_QUERY',				'Failed to execute a query. (_%1%_)');
	L('de', 'SQLITE_ERROR_NO_QUERY',					'Operation performed on a non-query.');
	L('de', 'SQLITEDB_ERROR_MISSING_SQLITE',			'The PHP SQLite extension is not installed on this system, aborting SQLite extension registration.');

	// Spanish error strings
	L('es', 'SQLITEDB_ERROR_INVALID_CONFIG',			'The configuration submitted for the SQLite database extension was invalid.');
	L('es', 'SQLITEDB_ERROR_FAILED_DBACCESS',			"The SQLite database extension was unable to access the '_%1%_' database for the following reason(s): _%2%_");
	L('es', 'SQLITEDB_ERROR_FAILED_DBOPEN',				"The SQLite database extension was unable to open for the following reason(s): _%1%_");
	L('es', 'SQLITEDB_ERROR_FAILED_QUERY',				'Failed to execute a query. (_%1%_)');
	L('es', 'SQLITE_ERROR_NO_QUERY',					'Operation performed on a non-query.');
	L('es', 'SQLITEDB_ERROR_MISSING_SQLITE',			'The PHP SQLite extension is not installed on this system, aborting SQLite extension registration.');

	// Swedish error strings
	L('se', 'SQLITEDB_ERROR_INVALID_CONFIG',			'The configuration submitted for the SQLite database extension was invalid.');
	L('se', 'SQLITEDB_ERROR_FAILED_DBACCESS',			"The SQLite database extension was unable to access the '_%1%_' database for the following reason(s): _%2%_");
	L('se', 'SQLITEDB_ERROR_FAILED_DBOPEN',				"The SQLite database extension was unable to open for the following reason(s): _%1%_");
	L('se', 'SQLITEDB_ERROR_FAILED_QUERY',				'Failed to execute a query. (_%1%_)');
	L('se', 'SQLITE_ERROR_NO_QUERY',					'Operation performed on a non-query.');
	L('se', 'SQLITEDB_ERROR_MISSING_SQLITE',			'The PHP SQLite extension is not installed on this system, aborting SQLite extension registration.');

	// Create our array of handlers
	$handlers = array(
		N2F_DBEVT_OPEN_CONNECTION					=> 'sqlite_openDatabase',
		N2F_DBEVT_CLOSE_CONNECTION					=> 'sqlite_closeDatabase',
		N2F_DBEVT_CHECK_CONNECTION					=> 'sqlite_checkDatabase',
		N2F_DBEVT_ADD_PARAMETER						=> 'sqlite_addParameter',
		N2F_DBEVT_EXECUTE_QUERY						=> 'sqlite_executeQuery',
		N2F_DBEVT_GET_ROW							=> 'sqlite_getRow',
		N2F_DBEVT_GET_ROWS							=> 'sqlite_getRows',
		N2F_DBEVT_GET_LAST_INC						=> 'sqlite_getLastInc',
		N2F_DBEVT_GET_NUMROWS						=> 'sqlite_getNumRows',
		N2F_DBEVT_GET_RESULT						=> 'sqlite_getResult'
	);

	// Check that the SQLite2 library is available
	if (function_exists('sqlite_open')) {
		n2f_database::addExtension('sqlite', $handlers);

		$n2f->registerExtension(
			'n2f_database/sqlite',
			'n2f_sqlite_database',
			0.1,
			'Chris Dougherty',
			'http://n2framework.com/'
		);
	} else {
		if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
			$n2f->debug->throwError(SQLITEDB_ERROR_MISSING_SQLITE, S('SQLITEDB_ERROR_MISSING_SQLITE'), 'n2f_database/sqlite.ext.php');
		}
	}

	/**
	 * SQLite extension handler for opening a database
	 *
	 * @param n2f_database $db	Current n2f_database object calling the handler
	 * @param n2f_cfg_db $cfg	Configuration value from n2f_cls::cfg for n2f_database object
	 * @return boolean
	 */
	function sqlite_openDatabase(n2f_database &$db, n2f_cfg_db $cfg) {
		$n2f = n2f_cls::getInstance();

		if (!isset($cfg->file)) {
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(SQLITEDB_ERROR_INVALID_CONFIG, S('SQLITEDB_ERROR_INVALID_CONFIG'), 'n2f_database/sqlite.ext.php');
			}

			$db->conn = false;

			return false;
		}
		else {
			// Configuration alright, open the database file
			$db->conn = sqlite_open($cfg->file, 0666, $open_error);

			if ($db->isOpen()) {
				if (!sqlite_query($db->conn, "SELECT * FROM sqlite_master LIMIT 1")) {
					if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
						$n2f->debug->throwError(SQLITEDB_ERROR_FAILED_DBACCESS, S('SQLITEDB_ERROR_FAILED_DBACCESS', array($cfg->name, sqlite_error_string(sqlite_last_error($db->conn)))), 'n2f_database/sqlite.ext.php');
					}

					$db->conn = false;

					return false;
				} else {
					if (!defined('SQLITEDB_CAN_CACHE')) {
						define('SQLITEDB_CAN_CACHE',		(bool)$n2f->hasExtension('n2f_cache'));
					}
				}
			} else {
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(SQLITEDB_ERROR_FAILED_DBOPEN, S('SQLITEDB_ERROR_FAILED_DBOPEN', array($open_error)), 'n2f_database/sqlite.ext.php');
				}

				return false;
			}
		}

		return true;
	}

	/**
	 * SQLite extension handler for closing a database handle.
	 *
	 * @param n2f_database $db	Current n2f_database object calling the handler
	 * @return boolean
	 */
	function sqlite_closeDatabase(n2f_database &$db) {
		if ($db->isOpen() === false) {
			return false;
		}

		@sqlite_close($db->conn);

		return true;
	}

	/**
	 * SQLite extension handler for checking if a database is open.
	 *
	 * @param n2f_database $db	Current n2f_database object calling the handler
	 * @return boolean
	 */
	function sqlite_checkDatabase(n2f_database $db) {
		if ($db->conn) {
			return true;
		}

		return false;
	}

	/**
	 * SQLite extension handler for adding a query parameter.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling the handler
	 * @param string $key				Key name for parameter
	 * @param mixed $value				Value for parameter
	 * @param integer $type				Type indicator for parameter
	 * @return boolean
	 */
	function sqlite_addParameter(n2f_database_query &$query, $key, $value, $type) {
		if ($query->validParamType($type) === false) {
			return false;
		}

		$value = ($type == N2F_DBTYPE_RAW_STRING) ? $value : "'" . sqlite_cleanParam($value) . "'";

		$query->params[] = array(
			'key'	=> $key,
			'value'	=> $value,
			'type'	=> $type
		);

		return true;
	}

	/**
	 * SQLite extension handler for executing a query.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling the handler
	 * @return boolean
	 */
	function sqlite_executeQuery(n2f_database_query &$query) {
		$n2f = n2f_cls::getInstance();

		// Strip out ticks (`) from the query
		$query->query = str_replace('`', '', $query->query);

		if (strlen($query->query) < 1) {
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(SQLITEDB_ERROR_FAILED_QUERY, S('SQLITEDB_ERROR_FAILED_QUERY', array('No query string was provided')), 'n2f_database/sqlite.ext.php');
			}
		}

		if (count($query->params) > 0) {
			$len = count($query->params);
			$pos = 0;

			for ($i = 0; $i < $len; $i++) {
				$query->query = str_replace_once('?', $query->params[$i]['value'], $query->query, $pos);
				$pos = strpos($query->query, '?', $pos) + strlen($query->params[$i]['value']);
			}
		}

		$res = @sqlite_query($query->db->conn, $query->query);

		if ($res) {
			$query->addData('res', $res);
		} else {
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(SQLITEDB_ERROR_FAILED_QUERY, S('SQLITEDB_ERROR_FAILED_QUERY', array(sqlite_error_string(sqlite_last_error($query->db->conn)))), 'n2f_database/sqlite.ext.php');
			}

			$query->addError(S('SQLITEDB_ERROR_FAILED_QUERY', array(sqlite_error_string(sqlite_last_error($query->db->conn)))));
		}

		return null;
	}

	/**
	 * SQLite extension handler for retrieving one row.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @return mixed
	 */
	function sqlite_getRow(n2f_database_query &$query) {
		$ret = null;

		$res = $query->getData('res');

		if ($res !== null) {
			if ($res) {
				if (@sqlite_num_rows($res) > 0) {
					$ret = @sqlite_fetch_array($res, SQLITE_ASSOC);
				}
			} else {
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(SQLITE_ERROR_FAILED_QUERY, S('SQLITEDB_ERROR_FAILED_QUERY', array('Invalid query for operation')), 'n2f_database/sqlite.ext.php');
				}
			}
		} else {
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(SQLITEDB_ERROR_NO_QUERY, S('SQLITEDB_ERROR_NO_QUERY'), 'n2f_database/sqlite.ext.php');
			}
		}

		return $ret;
	}

	/**
	 * SQLite extension handler for retrieving all rows from a query.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @return array
	 */
	function sqlite_getRows(n2f_database_query &$query) {
		$ret = array();

		$res = $query->getData('res');

		if ($res !== null) {
			if ($res) {
				if (@sqlite_num_rows($res) > 0) {
					while ($ret[] = @sqlite_fetch_array($res, SQLITE_ASSOC)) { }
					unset($ret[count($ret) - 1]);
				}
			} else {
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(SQLITE_ERROR_FAILED_QUERY, S('SQLITEDB_ERROR_FAILED_QUERY', array('Invalid query for operation')), 'n2f_database/sqlite.ext.php');
				}
			}
		} else {
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(SQLITEDB_ERROR_NO_QUERY, S('SQLITEDB_ERROR_NO_QUERY'), 'n2f_database/sqlite.ext.php');
			}
		}

		return $ret;
	}

	/**
	 * SQLite extension handler for retrieving the last AUTO_INCREMENT integer from a query, when available.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @return mixed
	 */
	function sqlite_getLastInc(n2f_database_query &$query) {
		$ret = null;

		$res = $query->getData('res');

		if ($res !== null) {
			if ($res) {
				$ret = @sqlite_last_insert_rowid($res);
			} else {
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(SQLITEDB_ERROR_FAILED_QUERY, S('SQLITEDB_ERROR_FAILED_QUERY', array('Invalid query for operation')), 'n2f_database/sqlite.ext.php');
				}
			}
		} else {
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(SQLITEDB_ERROR_NO_QUERY, S('SQLITEDB_ERROR_NO_QUERY'), 'n2f_database/sqlite.ext.php');
			}
		}

		return($ret);
	}

	/**
	 * SQLite extension handler for retrieving the number of rows returned by a query, if any.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @return integer
	 */
	function sqlite_getNumRows(n2f_database_query &$query) {
		$ret = 0;

		$res = $query->getData('res');

		if ($res !== null) {
			if ($res) {
				$ret = @sqlite_num_rows($res);
			} else {
				$n2f->debug->throwError(SQLITEDB_ERROR_FAILED_QUERY, S('SQLITEDB_ERROR_FAILED_QUERY', array('Invalid query for operation')), 'n2f_database/sqlite.ext.php');
			}
		} else {
			$n2f->debug->throwError(SQLITEDB_ERROR_NO_QUERY, S('SQLITEDB_ERROR_NO_QUERY'), 'n2f_database/sqlite.ext.php');
		}

		return($ret);
	}

	/**
	 * SQLite extension handler for retrieving a specific field from a specific row on a query.
	 *
	 * @param n2f_database_query $query	Current n2f_database_query object calling handler
	 * @param integer $offset			Row offset to pull field from
	 * @param string $field_name			Name of field to pull data from
	 * @return mixed
	 */
	function sqlite_getResult(n2f_database_query &$query, $offset, $field_name) {
		$ret = null;

		$res = $query->getData('res');

		if ($res !== null) {
			if ($res) {
				if (@sqlite_num_rows($res) > 0) {
					if ($offset == 0) {
						sqlite_rewind($res);
					} else {
						sqlite_seek($res, $offset);
					}

					$result = @sqlite_current($res, SQLITE_BOTH);

					$ret = $result[$field_name];
				}
			} else {
				if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
					$n2f->debug->throwError(SQLITEDB_ERROR_FAILED_QUERY, S('SQLITEDB_ERROR_FAILED_QUERY', array('Invalid query for operation')), 'n2f_database/sqlite.ext.php');
				}
			}
		} else {
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(SQLITEDB_ERROR_NO_QUERY, S('SQLITEDB_ERROR_NO_QUERY'), 'n2f_database/sqlite.ext.php');
			}
		}

		return $ret;
	}

	/**
	 * Returns a sanitized parameter for SQLite.
	 *
	 * @param mixed $value	Value to sanitize
	 * @return mixed
	 */
	function sqlite_cleanParam($value) {
		$value = sqlite_escape_string($value);
		$value = addcslashes($value, "\x00\n\r\'\x1a\x3c\x3e\x25");

		return($value);
	}

?>