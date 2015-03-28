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
	 * $Id: utilities.inc.php 157 2011-05-25 15:57:16Z amale@EPSILON $
	 */

	/**
	 * Runs main process for N2 Framework Yverdon.
	 *
	 * @return null
	 */
	function n2f_proc() {
		n2f_cls::getInstance()->initModule();

		return(null);
	}

	/**
	 * Pulls a string from the system's string set, given the current system language.
	 *
	 * @param string $key			Key to search strings definition for
	 * @param array $replacements		Optional array of arguments to replace in the defined string
	 * @return string
	 */
	function S($key, array $replacements = null) {
		global $strings;
		$n2f = n2f_cls::getInstance();

		if (!isset($strings[$n2f->cfg->sys_lang])) {
			if ($n2f->debug->showLevel(N2F_DEBUG_ERROR)) {
				$n2f->debug->throwError(N2F_ERROR_NO_LANGUAGE_SET, S('N2F_ERROR_NO_LANGUAGE_SET'), 'system/config.inc.php');
			}

			return('N/A');
		}

		if (!isset($strings[$n2f->cfg->sys_lang][$key])) {
			if ($n2f->debug->showLevel(N2F_DEBUG_WARN)) {
				$n2f->debug->throwWarning(N2F_WARN_LANG_KEY_MISSING, S('N2F_WARN_LANG_KEY_MISSING', array($key)), 'system/includes/strings.inc.php');
			}

			if (!isset($strings['en'][$key])) {
				return('N/A');
			} else {
				$lang = 'en';
			}
		} else {
			$lang = $n2f->cfg->sys_lang;
		}

		$string = $strings[$lang][$key];

		if ($replacements !== null) {
			for ($i = 1; $i <= count($replacements); $i++) {
				$string = str_replace("_%{$i}%_", $replacements[$i - 1], $string);
			}
		}

		return($string);
	}

	/**
	 * Inserts a string into the system's string set.
	 *
	 * @param string $lang	String value of the language specifier.
	 * @param string $key	String value of the string key (for retrieval).
	 * @param string $string	String value to insert into system set.
	 * @return boolean		Boolean TRUE or FALSE depending on the insert's success.
	 */
	function L($lang, $key, $string) {
		global $strings;
		$n2f = n2f_cls::getInstance();

		if (!isset($strings[$lang])) {
			$strings[$lang] = array();
		}

		if (isset($strings[$lang][$key])) {
			if ($n2f->debug->showLevel(N2F_DEBUG_WARN)) {
				$n2f->debug->throwWarning(N2F_WARN_LANG_KEY_EXISTS, S('N2F_WARN_LANG_KEY_EXISTS', array($key)), 'system/includes/utilities.inc.php');
			}

			return(false);
		}

		$strings[$lang][$key] = $string;

		if ($n2f->debug->showLevel(N2F_DEBUG_NOTICE)) {
			$n2f->debug->throwNotice(N2F_NOTICE_LANG_KEY_SET, S('N2F_NOTICE_LANG_KEY_SET', array($key)), 'system/includes/utilities.inc.php');
		}

		return(true);
	}

	/**
	 * Returns a string based on the callback provided.
	 *
	 * @param callback $callback	Callback to return as a string
	 * @return string
	 */
	function callback_toString($callback) {
		$ret = '';

		if (is_array($callback)) {
			if (is_object($callback[0])) {
				$ret .= get_class($callback[0]);
			} else {
				$ret .= $callback[0];
			}

			$ret .= "::{$callback[1]}";
		} else {
			$ret = $callback;
		}

		return($ret);
	}

	/**
	 * Creates a one-way hash from the provided string.  Setting $old to true will use the old algorithm for creating the hash.
	 *
	 * @param string $str	String to hash
	 * @param boolean $old	Whether or not to use the old ZSF algorithm
	 * @return string
	 */
	function encStr($str, $old = false) {
		$n2f = n2f_cls::getInstance();

		if ($old) {
			$ret = '';

			for ($i = 0; $i < strlen($str); ++$i) {
				$ret .= md5(base64_encode($str[$i]));
			}

			return(md5(base64_encode($ret)));
		}

		$ret = '';

		if ($n2f instanceof n2f_cls && $n2f->cfg instanceof n2f_cfg) {
			$str .= $n2f->cfg->crypt_hash;
		}

		for ($i = 0; $i < strlen($str); ++$i) {
			$ret .= md5(sha1(base64_encode($str[$i])));
		}

		return(md5(sha1(base64_encode($ret))));
	}

	/**
	 * Hand-made print_r() that provides a method list if $showMethods is set to true.
	 *
	 * @param mixed $mixed			Item to break down and display
	 * @param boolean $return		Whether or not to return the output from debugEcho()
	 * @param boolean $showMethods	Whether or not to show methods on objects
	 * @param mixed $prefix			Internal for handling recursion
	 * @return null
	 */
	function debugEcho($mixed, $return = false, $showMethods = false, $prefix = false) {
		// if no prefix
		if ($prefix == false) {
			// setup dummy prefix
			$prefix = '';
		}

		// if it's false, we can assume we're at the beginning
		if ($prefix == false) {
			// If we're to return this
			if ($return === true) {
				// Start the output buffering
				ob_start();
			}

			// so output a bit of styling
			echo("<div style='text-align: left;'>");
			echo('<pre>');
		} else {
			// just add a newline
			echo("\n");
		}

		// if it's an array
		if (is_array($mixed)) {
			// display the header
			echo("{$prefix}Array Elements {\n");

			// if there aren't any
			if (count($mixed) < 1) {
				// show that we're runnin on emptyyyy
				echo("{$prefix}  No Elements;\n");
			} else {
				// loop through the elements
				foreach ($mixed as $key => $ele) {
					// if it's an array or object
					if (is_array($ele) || is_object($ele)) {
						// show the type
						echo("{$prefix}  [{$key}] => (".gettype($ele).") {");

						// loop through it again
						debugEcho($ele, $return, $showMethods, "{$prefix}    ");

						// and show the end of it
						echo("\n{$prefix}  };\n");
					} else {
						// show the element
						echo("{$prefix}  [{$key}] => (".gettype($ele).") '{$ele}';\n");
					}
				}
			}

			// and the end-of
			echo("{$prefix}};");
		} else if (is_object($mixed)) {
			// say what kind
			echo("{$prefix}".get_class($mixed)." Object:\n\n{$prefix}Public Properties {\n");

			// get the properties
			$vars = get_object_vars($mixed);

			// if there aren't any properties
			if (count($vars) < 1) {
				// show that there aren't any
				echo("{$prefix}  No Properties;\n");
			} else {
				// loop through the properties
				foreach ($vars as $prop => $valu) {
					// if it's the special bugger
					if ($prop == '_loadedMethods') {
						// throw them into a temp variable
						$_loadedMethods = $valu;

						// and skip this iteration
						continue;
					}

					// if it's an array or it's an object
					if (is_array($valu) || is_object($valu)) {
						// show the property and value
						echo("{$prefix}  [{$prop}] => (".gettype($valu).") {");

						// and throw it into another discovery mission
						debugEcho($valu, $return, $showMethods, "{$prefix}    ");

						// and get back
						echo("\n{$prefix}  };\n");
					} else {
						// show the property and value
						echo("{$prefix}  [{$prop}] => (".gettype($valu).") '{$valu}';\n");
					}
				}
			}

			// throw in an end-of bracket
			echo("{$prefix}};\n");

			// if we're to show the methods
			if ($showMethods) {
				// greet the audience
				echo("\n{$prefix}Public Methods {\n");

				// grab the methods we can see
				$methods = get_class_methods(get_class($mixed));

				// if we have other methods
				if (isset($_loadedMethods) && count($_loadedMethods) > 0) {
					// loop through THEM
					foreach (array_keys($_loadedMethods['methods']) as $method) {
						// and add them onto the end of the pile
						$methods[] = $method;
					}
				}

				// if we have 0 methods
				if (count($methods) < 1) {
					// say so!
					echo("{$prefix}  No Methods;\n");
				} else {
					// loop through them
					foreach ($methods as $method) {
						// if it's an array
						if (is_array($method)) {
							// show the one it'd be called by
							echo("{$prefix}  {$method[1]};\n");
						} else {
							// show the method name
							echo("{$prefix}  {$method};\n");
						}
					}
				}

				// and do our end-of
				echo("{$prefix}};\n");
			}

			// and the OFFICIAL end-of
			echo("\n{$prefix}End of ".get_class($mixed)." Object");
		} else {
			// just a catch-all, shouldn't ever use this
			echo("{$prefix}UNKNOWN of type [".gettype($mixed)."]: '{$mixed}';\n");
		}

		// if we're at the 'top' level
		if ($prefix == false) {
			// print the bye bye
			echo('</pre>');
			echo("</div>");

			// If we've been buffering for output
			if ($return === true) {
				// Grab the output
				$output = ob_get_contents();

				// End cleanly
				ob_end_clean();
			} else {
				// Otherwise, set to null
				$output = null;
			}
		}

		if (!isset($output)) {
			$output = null;
		}

		// return safely
		return($output);
	}

	/**
	 * Tries to find and replace the search string inside of the subject string once.
	 *
	 * @param string $search		String to find inside of $subject string
	 * @param string $replace	String to replace first instance of $search string
	 * @param string $subject	String to perform actions upon
	 * @param integer $pos		Integer value to start search from
	 * @return string
	 */
	function str_replace_once($search, $replace, $subject, $pos = 0) {
		if (strlen($search) < 1 || strlen($replace) < 1 || strlen($search) > strlen($subject)) {
			return($subject);
		}

		$offset = strpos($subject, $search, $pos);

		if ($offset === false) {
			return($subject);
		}

		$search_len = strlen($search);
		$subject_len = strlen($subject);
		$after_start = ($offset + $search_len);
		$after_len = ($subject_len - ($offset - $search_len));

		$before = substr($subject, 0, $offset);
		$after = substr($subject, $after_start, $after_len);

		return($before . $replace . $after);
	}

	/**
	 * Finds and replaces text contained within the $start and $end tags with the $replace sequence.  Use of the text between $start and $end is signified in the $replace string using the string '%TEXT%'.
	 *
	 * @param string $start		Beginning tag surrounding text
	 * @param string $end		End tag surrounding text
	 * @param string $replace	String to replace tags and inner text with (reference inner text with %TEXT%)
	 * @param string $text		Subject string to perform search/replace within
	 * @return string
	 */
	function str_replace_contained($start, $end, $replace, $text) {
		if (strlen($text) <= (strlen($start) + strlen($end))) {
			return($text);
		}

		$s = array(
			'spos'	=> strpos($text, $start),
			'epos'	=> null,
			'len'	=> strlen($start),
			'text'	=> null
		);

		$e = array(
			'spos'	=> strpos($text, $end),
			'epos'	=> null,
			'len'	=> strlen($end),
			'text'	=> null
		);

		if ($s['spos'] === false || $e['spos'] === false) {
			return($text);
		}

		$s['epos'] = $s['spos'] + $s['len'];
		$e['epos'] = $e['epos'] + $e['len'];

		if ($s['epos'] == ($e['spos'] - 1)) {
			return($text);
		}

		$inner_text = substr($text, $s['epos'], ($e['spos'] - $s['epos']));
		$replace = str_replace('%TEXT%', $inner_text, $replace);
		$text = str_replace($start . $inner_text . $end, $replace, $text);

		return($text);
	}

?>