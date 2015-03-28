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
	 * $Id: debug.cls.php 186 2012-01-14 04:12:47Z amale@EPSILON $
	 */

	/**
	 * Debug utility class for N2 Framework Yverdon.
	 *
	 */
	class n2f_debug extends n2f_events {
		/**
		 * Internal n2f_cfg_dbg configuration object.
		 *
		 * @var n2f_cfg_dbg
		 */
		protected $config;
		/**
		 * Internal list of errors produced by system.
		 *
		 * @var array
		 */
		protected $errors;
		/**
		 * Internal list of warnings produced by system.
		 *
		 * @var array
		 */
		protected $warnings;
		/**
		 * Internal list of notices produced by system.
		 *
		 * @var array
		 */
		protected $notices;

		/**
		 * Initializes a new n2f_debug object.
		 *
		 * @param mixed $cfg	Optional configuration values in either array or n2f_cfg_dbg format.
		 * @return n2f_debug	The new n2f_debug object.
		 */
		public function __construct($cfg = null) {
			// Initialize event system
			parent::__construct();

			// If not config provided
			if ($cfg == null) {
				// Create a default config object
				$cfg = new n2f_cfg_dbg();
				$cfg->dump_debug = false;
				$cfg->level = N2F_DEBUG_OFF;
			} else { // Otherwise try to set up based on info provided
				// Array of values can be used as ctor argument
				if (is_array($cfg)) {
					$this->config = new n2f_cfg_dbg($cfg);
				} else if ($cfg instanceof n2f_cfg_dbg) { // Or just an actual config object
					$this->config = $cfg;
				}
			}

			// Initialize arrays
			$this->errors = array();
			$this->warnings = array();
			$this->notices = array();

			// Add our events
			$this->addEvent(N2F_EVT_ERROR_THROWN);
			$this->addEvent(N2F_EVT_WARNING_THROWN);
			$this->addEvent(N2F_EVT_NOTICE_THROWN);

			// I don't know why I keep doing this, can't help myself...
			return($this);
		}

		/**
		 * Adds an error to the internal list.
		 *
		 * @param integer $errno		Error number being reported
		 * @param string $errstr		Error string being reported
		 * @param string $file		File where error was recorded from
		 * @return n2f_debug		The current n2f_debug object.
		 */
		public function throwError($errno, $errstr, $file) {
			// Enter data into array
			$this->errors[] = array(
				'num'	=> $errno,
				'str'	=> $errstr,
				'file'	=> $file,
				'time'	=> time(),
			);

			// Trigger notification event
			$this->hitEvent(N2F_EVT_ERROR_THROWN, array($errno, $errstr, $file));

			// Return for chaining
			return($this);
		}

		/**
		 * Adds a warning to the internal list.
		 *
		 * @param integer $warno		Warning number being reported
		 * @param string $warstr		Warning string being reported
		 * @param string $file		File where warning was recorded from
		 * @return n2f_debug		The current n2f_debug object.
		 */
		public function throwWarning($warno, $warstr, $file) {
			// Enter data into array
			$this->warnings[] = array(
				'num'	=> $warno,
				'str'	=> $warstr,
				'file'	=> $file,
				'time'	=> time()
			);

			// Trigger notification event
			$this->hitEvent(N2F_EVT_WARNING_THROWN, array($warno, $warstr, $file));

			// Return for chaining
			return($this);
		}

		/**
		 * Adds a notice to the internal list.
		 *
		 * @param integer $notno		Notice number being reported
		 * @param string $notstr		Notice string being reported
		 * @param string $file		File where notice was recorded from
		 * @return n2f_debug		The current n2f_debug object.
		 */
		public function throwNotice($notno, $notstr, $file) {
			// Enter data into array
			$this->notices[] = array(
				'num'	=> $notno,
				'str'	=> $notstr,
				'file'	=> $file,
				'time'	=> time()
			);

			// Trigger notification event
			$this->hitEvent(N2F_EVT_NOTICE_THROWN, array($notno, $notstr, $file));

			// Return for chaining
			return($this);
		}

		/**
		 * Returns the internal list of errors.
		 *
		 * @return array	Array of errors.
		 */
		public function getErrors() {
			// Simply return the error array
			return($this->errors);
		}

		/**
		 * Returns the internal list of warnings.
		 *
		 * @return array	Array of warnings.
		 */
		public function getWarnings() {
			// Simply return the warning array
			return($this->warnings);
		}

		/**
		 * Returns the internal list of notices.
		 *
		 * @return array	Array of notices.
		 */
		public function getNotices() {
			// Simply return the notice array
			return($this->notices);
		}

		/**
		 * Returns true or false depending on whether or not the provided debug level is currently toggled.
		 *
		 * @param integer $level		Level to compare against for toggle
		 * @return boolean			Boolean value based on curent debug level.
		 */
		public function showLevel($level) {
			// Test configured level against requested level
			if ($this->config->level >= $level) {
				// We are that level or better
				return(true);
			}

			// Not in the cards today fella
			return(false);
		}
	}

?>