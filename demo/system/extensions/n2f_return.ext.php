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
	 * $Id: n2f_return.ext.php 192 2012-01-19 20:01:11Z amale@EPSILON $
	 */

	// Pull in globals
	$n2f = n2f_cls::getInstance();

	// Register extension
	$n2f->registerExtension(
		'return',
		'n2f_return',
		0.2,
		'Andrew Male',
		'http://n2framework.com/'
	);

	/**
	 * Return class for N2 Framework Yverdon
	 *
	 */
	class n2f_return {
		/**
		 * Current status of the n2f_return object.
		 *
		 * @var boolean
		 */
		public $sts;
		/**
		 * List of messages for the n2f_return object.
		 *
		 * @var array
		 */
		public $msgs;
		/**
		 * Data contained by n2f_return object.
		 *
		 * @var mixed
		 */
		public $data;

		// Status constants
		const stat_good	= true;
		const stat_fail	= false;

		/**
		 * Initializes a new n2f_return object.
		 *
		 * @return n2f_return
		 */
		public function __construct() {
			// Initialize our properties
			$this->sts = self::stat_fail;
			$this->msgs = array();
			$this->data = null;

			// Return ourself for chaining
			return($this);
		}

		/**
		 * Adds a new message to the n2f_return object.
		 *
		 * @param string $str	Message string to add to the stack
		 * @return n2f_return
		 */
		public function addMsg($str) {
			// Add a message to the stack
			$this->msgs[] = $str;

			// Return ourself for chaining
			return($this);
		}

		/**
		 * Shows whether or not the n2f_return object has messages.
		 *
		 * @return boolean
		 */
		public function hasMsgs() {
			// Check if there are messages in the stack
			if (is_array($this->msgs) && count($this->msgs) > 0) {
				// Return true because there are messages
				return(true);
			}

			// Return false because there aren't messages
			return(false);
		}

		/**
		 * Sets the n2f_return object to be in "good" status.
		 *
		 * @return n2f_return
		 */
		public function isGood() {
			// Set the status to be 'good'
			$this->sts = self::stat_good;

			// Return ourself for chaining
			return($this);
		}

		/**
		 * Sets the n2f_return object to be in "fail" status.
		 *
		 * @return n2f_return
		 */
		public function isFail() {
			// Set the status to be 'fail'
			$this->sts = self::stat_fail;

			// Return ourself for chaining
			return($this);
		}

		/**
		 * Method to determine if the result was a failure or a success.  (True if failure, false if success)
		 *
		 * @return boolean
		 */
		public function failed() {
			if ($this->sts) {
				return(false);
			}

			return(true);
		}

		/**
		 * Alias method for the IsSuccess() function.
		 *
		 * @return boolean
		 */
		public function isSuccess() {
			return(IsSuccess($this));
		}
	}

	/**
	 * Function to determine if an n2f_return object was a success.
	 *
	 * @param n2f_return $RetObj	n2f_return object to check for success.
	 * @return boolean
	 */
	function IsSuccess(n2f_return $RetObj) {
		if ($RetObj->sts == n2f_return::stat_good) {
			return(true);
		}

		return(false);
	}

?>