<?php

	// Database Global Types
	define('N2F_DBTYPE_INTEGER',							0);
	define('N2F_DBTYPE_DOUBLE',							1);
	define('N2F_DBTYPE_STRING',							2);
	define('N2F_DBTYPE_BINARY',							3);
	define('N2F_DBTYPE_RAW_STRING',						4);
	define('N2F_DBTYPE_LIKE_STRING',						5);

	// N2 Framework Yverdon Database Event Constants
	define('N2F_DBEVT_OPEN_CONNECTION',					'N2F_DBEVT_OPEN_CONNECTION');
	define('N2F_DBEVT_CLOSE_CONNECTION',					'N2F_DBEVT_CLOSE_CONNECTION');
	define('N2F_DBEVT_CHECK_CONNECTION',					'N2F_DBEVT_CHECK_CONNECTION');
	define('N2F_DBEVT_ADD_PARAMETER',						'N2F_DBEVT_ADD_PARAMETER');
	define('N2F_DBEVT_EXECUTE_QUERY',						'N2F_DBEVT_EXECUTE_QUERY');
	define('N2F_DBEVT_GET_ROW',							'N2F_DBEVT_GET_ROW');
	define('N2F_DBEVT_GET_ROWS',							'N2F_DBEVT_GET_ROWS');
	define('N2F_DBEVT_GET_LAST_INC',						'N2F_DBEVT_GET_LAST_INC');
	define('N2F_DBEVT_GET_NUMROWS',						'N2F_DBEVT_GET_NUMROWS');
	define('N2F_DBEVT_GET_RESULT',						'N2F_DBEVT_GET_RESULT');
	define('N2F_DBEVT_ENGINE_REGISTERED',					'N2F_DBEVT_ENGINE_REGISTERED');
	define('N2F_DBEVT_HANDLER_CREATED',					'N2F_DBEVT_HANDLER_CREATED');
	define('N2F_DBEVT_CONNECTION_OPENED',					'N2F_DBEVT_CONNECTION_OPENED');
	define('N2F_DBEVT_CONNECTION_CLOSED',					'N2F_DBEVT_CONNECTION_CLOSED');
	define('N2F_DBEVT_QUERY_CREATED',						'N2F_DBEVT_QUERY_CREATED');
	define('N2F_DBEVT_PARAMETER_ADDED',					'N2F_DBEVT_PARAMETER_ADDED');
	define('N2F_DBEVT_QUERY_EXECUTED',						'N2F_DBEVT_QUERY_EXECUTED');
	define('N2F_DBEVT_ROW_RETRIEVED',						'N2F_DBEVT_ROW_RETRIEVED');
	define('N2F_DBEVT_ROWS_RETRIEVED',						'N2F_DBEVT_ROWS_RETRIEVED');
	define('N2F_DBEVT_LAST_INC_RETRIEVED',					'N2F_DBEVT_LAST_INC_RETRIEVED');
	define('N2F_DBEVT_NUMROWS_RETRIEVED',					'N2F_DBEVT_NUMROWS_RETRIEVED');
	define('N2F_DBEVT_RESULT_RETRIEVED',					'N2F_DBEVT_RESULT_RETRIEVED');

	// Database Error Number Constants
	define('N2F_ERROR_DB_EXTENSION_NOT_LOADED',				'0001');
	define('N2F_ERROR_DB_EXTENSION_EMPTY',					'0002');
	define('N2F_ERROR_DB_NOT_LOADED',						'0003');
	define('N2F_ERROR_DB_INVALID_STORED_QUERY',				'0004');

	// Database Notice Number Constants
	define('N2F_NOTICE_DB_EXTENSION_LOADED',				'0001');
	define('N2F_NOTICE_DB_CONNECTION_OPENED',				'0002');
	define('N2F_NOTICE_DB_CONNECTION_CLOSED',				'0003');
	define('N2F_NOTICE_DB_QUERY_CREATED',					'0004');
	define('N2F_NOTICE_DB_PARAMETER_ADDED',					'0005');
	define('N2F_NOTICE_DB_QUERY_EXECUTED',					'0006');

	// Database Warning Number Constants
	define('N2F_WARN_DB_PARAMETERS_NOT_SUPPLIED',			'0001');
	define('N2F_WARN_DB_INCORRECT_STORED_PARAMETER_COUNT',		'0002');

?>