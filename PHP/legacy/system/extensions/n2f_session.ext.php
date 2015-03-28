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
	 * $Id: n2f_session.ext.php 192 2012-01-19 20:01:11Z amale@EPSILON $
	 */

	// Pull/create the global variables needed
	$n2f = n2f_cls::getInstance();

	// Register extension
	$n2f->registerExtension(
		'session',
		'n2f_session',
		'0.3',
		'Andrew Male',
		'http://n2framework.com/'
	);

	// assign the session initialization function
	$n2f->hookEvent(N2F_EVT_CORE_LOADED, 'init_session');

	/**
	 * Initializes the session extension.
	 *
	 * @param n2f_cls $n2f	Current n2f_cls object calling the handler
	 * @param mixed $results	Results returned from the N2F_EVT_DATABASE_LOADED event
	 * @return null
	 */
	function init_session(n2f_cls &$n2f, $results) {
		if (!$n2f->hasExtension('session') || $results === false) {
			return(null);
		}

		global $sess;

		$sess = n2f_session::getInstance();
		$sess->start();

		return(null);
	}


	/**
	 * Extension class for doing session work in N2 Framework Yverdon.
	 *
	 */
	class n2f_session {
		/**
		 * Holds the tag, if any, of the current session object.
		 *
		 * @var string
		 */
		protected $tag;
		/**
		 * Holds the current name used by the session.
		 *
		 * @var string
		 */
		protected $name;
		/**
		 * Holds the current session id used by the session.
		 *
		 * @var string
		 */
		protected $sid;
		/**
		 * Protected static property to hold singleton global.
		 *
		 * @var n2f_session
		 */
		protected static $_instance = null;


		/**
		 * Method to get the current n2f_session instance.
		 *
		 * @return n2f_session
		 */
		public static function &getInstance($cfg = null, $tag = null) {
			if (n2f_session::$_instance === null) {
				n2f_session::$_instance = new n2f_session($cfg, $tag);
			}

			if ($tag === null) {
				$instance = n2f_session::$_instance;
			} else {
				$instance = new n2f_session($GLOBALS['cfg'], $tag);
			}

			return($instance);
		}


		/**
		 * Initializes a new n2f_session object.
		 *
		 * @return n2f_session
		 */
		public function __construct($cfg = null, $tag = null) {
			if ($cfg === null || !is_array($cfg)) {
				$cfg = $GLOBALS['cfg'];
			}

			if (isset($cfg['sess']) && is_array($cfg['sess'])) {
				$this->name	= (isset($cfg['sess']['name']) && !empty($cfg['sess']['name'])) ? $cfg['sess']['name'] : null;
				$this->sid	= (isset($cfg['sess']['sid']) && !empty($cfg['sess']['sid'])) ? $cfg['sess']['sid'] : null;
			} else {
				$this->name	= null;
				$this->sid	= null;
			}

			$this->tag = $tag;

			if ($this->tag !== null && !isset($_SESSION)) {
				n2f_session::$_instance = new n2f_session($cfg);
			} else if (!isset($_SESSION[$this->tag])) {
				$_SESSION[$this->tag] = array();
			}
		}

		/**
		 * Sets the session identifier.
		 *
		 * @param string $str	Identifier string for session.
		 * @return n2f_session
		 */
		public function setId($str) {
			if ($this->tag !== null && !empty($this->tag)) {
				return($this);
			}

			$this->sid = $str;

			return($this);
		}

		/**
		 * Sets the session name.
		 *
		 * @param string $str	Name of session.
		 * @return n2f_session
		 */
		public function setName($str) {
			if ($this->tag !== null && !empty($this->tag)) {
				return($this);
			}

			$this->name = $str;

			return($this);
		}

		/**
		 * Starts the PHP session.
		 *
		 * @return n2f_session
		 */
		public function start() {
			if ($this->name !== null) {
				session_name($this->name);
			}

			if ($this->sid !== null) {
				session_id($this->sid);
				session_start();
			} else {
				session_start();
				$this->sid = session_id();
			}

			return($this);
		}

		/**
		 * Checks if a variable exists in the current session.
		 *
		 * @param string $name	Name of the variable to check
		 * @return boolean
		 */
		public function exists($name) {
			if ($this->tag !== null && !empty($this->tag)) {
				if (isset($_SESSION[$this->tag][$name])) {
					return(true);
				}

				return(false);
			}

			if (isset($_SESSION[$name])) {
				return(true);
			}

			return(false);
		}

		/**
		 * Sets a variable in the current session.
		 *
		 * @param string $name	Name of the variable to create
		 * @param mixed $val	Value of the created variable
		 * @return n2f_session
		 */
		public function set($name, $val) {
			if ($this->tag !== null && !empty($this->tag)) {
				$_SESSION[$this->tag][$name] = $val;
			} else {
				$_SESSION[$name] = $val;
			}

			return($this);
		}

		/**
		 * Retrieves a variable from the current session.
		 *
		 * @param string $name	Name of the variable to retrieve
		 * @return mixed
		 */
		public function get($name) {
			return(($this->tag !== null && !empty($this->tag)) ? $_SESSION[$this->tag][$name] : $_SESSION[$name]);
		}

		/**
		 * Deletes a variable from the current session.
		 *
		 * @param string $name	Name of the variable to delete
		 * @return n2f_session
		 */
		public function delete($name) {
			if ($this->tag !== null && !empty($this->tag)) {
				unset($_SESSION[$this->tag][$name]);
			} else {
				unset($_SESSION[$name]);
			}

			return($this);
		}

		/**
		 * Destroys the current session (as well as all variables in the current session)
		 *
		 * @return n2f_session
		 */
		public function destroy() {
			if ($this->tag !== null && !empty($this->tag)) {
				unset($_SESSION[$this->tag]);
			} else {
				unset($_SESSION);
				session_destroy();
			}

			return($this);
		}
	}

?>