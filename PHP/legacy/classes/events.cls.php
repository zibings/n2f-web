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
	 * $Id: events.cls.php 186 2012-01-14 04:12:47Z amale@EPSILON $
	 */

	/**
	 * Core event class for N2 Framework Yverdon
	 *
	 */
	class n2f_events {
		/**
		 * Contains an array of events and hooked callbacks.
		 *
		 * @var array
		 */
		protected $events;

		/**
		 * Initializes the n2f_events object.
		 *
		 * @return n2f_events
		 */
		public function __construct() {
			// Initialize array
			$this->events = array();

			// I mean, I guess it's for chaining, but you can't do that in PHP
			return($this);
		}

		/**
		 * Adds an event to the n2f_events object stack.
		 *
		 * @param string $name	Name of the event to make available
		 * @return n2f_events
		 */
		protected function addEvent($name, $sys_evt = false) {
			// Add event information to array
			$this->events[$name] = array(
				'sys'	=> $sys_evt,
				'hooks'	=> array(),
				'count'	=> 0,
				'running'	=> false
			);

			// Return for chaining
			return($this);
		}

		/**
		 * Causes an event in the n2f_events object stack to be 'hit' or 'bubbled'.
		 *
		 * @param string $name	Name of the event to hit/bubble
		 * @param array $args	Arguments to pass to event hooks
		 * @return array
		 */
		protected function hitEvent($name, array $args = null) {
			// Make sure event is in stack and has callbacks
			if (!isset($this->events[$name]) || $this->events[$name]['count'] < 1) {
				return(false);
			}

			// Make sure event isn't running, don't want any recursion
			if ($this->events[$name]['running'] !== false) {
				return(false);
			}

			// If we're here, we're not running yet, so we are running now
			$this->events[$name]['running'] = true;

			// If no arguments, setup blank array as placeholder
			if ($args === null) {
				$args = array();
			}

			// Define our results variable
			$results = array();

			// Loop through any/all callbacks for event
			foreach (array_values($this->events[$name]['hooks']) as $callback) {
				// Make sure callback is usable
				if (is_callable($callback)) {
					// If it's a system event, just go ahead and set results to the result
					if ($this->events[$name]['sys'] === true) {
						$results = call_user_func_array($callback, $args);
					} else { // Otherwise, keep adding the results to the array
						$results[] = array(
							'callback'	=> callback_toString($callback),
							'returned'	=> call_user_func_array($callback, $args)
						);
					}
				}
			}

			// Now we're done, so stop running
			$this->events[$name]['running'] = false;

			// Return the results, array or otherwise
			return($results);
		}

		/**
		 * Attaches a callback to an event in the n2f_events object stack.
		 *
		 * @param string $name		Name of the event to hook to
		 * @param callback $callback	Callback method/function to hook to event
		 * @return boolean
		 */
		public function hookEvent($name, $callback) {
			// Make sure the event exists
			if (!isset($this->events[$name])) {
				return(false);
			}

			// If it's a system event, overwrite callbacks to be the new one
			if ($this->events[$name]['sys'] === true) {
				$this->events[$name]['hooks'] = array($callback);
				$this->events[$name]['count'] = 1;
			} else { // Otherwise add it to the callback list
				$this->events[$name]['hooks'][] = $callback;
				$this->events[$name]['count']++;
			}

			return(true);
		}
	}

?>